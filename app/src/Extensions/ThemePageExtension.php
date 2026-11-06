<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Elements\CTAElement;
use App\Elements\HeroElement;
use App\Elements\Items\Place;
use App\Geo\RouteService;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Controllers\RootURLController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\i18n\i18n;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Theme helpers on every page.
 *
 * @property string|null $MetaTitle
 * @method Image HeaderImage()
 * @extends Extension<SiteTree>
 */
class ThemePageExtension extends Extension
{
    private static array $db = [
        'MetaTitle' => 'Varchar(80)',
    ];

    private static array $has_one = [
        'HeaderImage' => Image::class,
    ];

    private static array $owns = [
        'HeaderImage',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldToTab(
            'Root.Main',
            UploadField::create('HeaderImage', 'Header image')
                ->setFolderName('Pages')
                ->setAllowedFileCategories('image/supported')
                ->setDescription('Optional. Ignored when the first block on the page is a Hero.'),
            'Content',
        );

        $metaTitle = TextField::create('MetaTitle', 'Meta title')
            ->setMaxLength(80)
            ->setDescription('Browser tab / Google title, 50–60 characters. Empty = page title. The site name is appended automatically.');

        if ($fields->dataFieldByName('MetaDescription')) {
            $fields->insertBefore('MetaDescription', $metaTitle);
        } else {
            $fields->addFieldToTab('Root.Main', $metaTitle);
        }
    }

    // =====================================================================
    // SEO
    // =====================================================================

    public function IsHome(): bool
    {
        return (int) $this->owner->ParentID === 0
            && $this->owner->URLSegment === RootURLController::get_homepage_link();
    }

    /**
     * <title>: meta title (or page title) + site name, without doubling the name.
     */
    public function DocumentTitle(): string
    {
        $site = (string) SiteConfig::current_site_config()->Title;
        $title = trim((string) ($this->owner->MetaTitle ?: $this->owner->Title));

        if ($site === '' || stripos($title, $site) !== false) {
            return $title;
        }

        return $title . ' · ' . $site;
    }

    public function OGType(): string
    {
        return is_a($this->owner, 'SilverStripe\\Blog\\Model\\BlogPost') ? 'article' : 'website';
    }

    public function OGLocale(): string
    {
        return (string) i18n::get_locale();
    }

    public function OGImageURL(): string
    {
        $image = $this->owner->HeaderImage();

        if ((!$image || !$image->exists()) && $this->owner->hasMethod('FeaturedImage')) {
            $image = $this->owner->FeaturedImage();
        }

        if ((!$image || !$image->exists()) && ($hero = $this->firstElement()) instanceof HeroElement && $hero->hasMethod('Slides')) {
            $image = $hero->Slides()->first();
        }

        if (!$image || !$image->exists()) {
            return '';
        }

        $resized = $image->FocusFill(1200, 630);

        return $resized ? (string) $resized->getAbsoluteURL() : (string) $image->getAbsoluteURL();
    }

    /**
     * Place (beach / destination) linked to this page – distance box on articles.
     */
    public function RelatedPlace(): ?Place
    {
        if (!class_exists(Place::class) || !$this->owner->ID) {
            return null;
        }

        return Place::get()->filter('ArticleID', $this->owner->ID)->sort('Sort')->first();
    }

    /**
     * JSON-LD: VacationRental on the home page, BlogPosting on articles,
     * BreadcrumbList everywhere except home.
     */
    public function StructuredData(): DBHTMLText
    {
        $graphs = [];

        if ($this->IsHome()) {
            $graphs[] = $this->lodgingSchema();
            $graphs[] = [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => (string) SiteConfig::current_site_config()->Title,
                'url' => Director::absoluteBaseURL(),
                'inLanguage' => str_replace('_', '-', i18n::get_locale()),
            ];
        } else {
            $graphs[] = $this->breadcrumbSchema();
        }

        if (is_a($this->owner, 'SilverStripe\\Blog\\Model\\BlogPost')) {
            $graphs[] = $this->articleSchema();
        }

        $html = '';
        foreach (array_filter($graphs) as $graph) {
            $html .= '<script type="application/ld+json">'
                . json_encode($graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG)
                . '</script>' . "\n";
        }

        return DBField::create_field('HTMLFragment', $html);
    }

    /**
     * @return array<string, mixed>
     */
    private function lodgingSchema(): array
    {
        $config = SiteConfig::current_site_config();
        $data = [
            '@context' => 'https://schema.org',
            '@type' => ['LodgingBusiness', 'VacationRental'],
            'name' => (string) $config->Title,
            'url' => Director::absoluteBaseURL(),
            'description' => (string) ($this->owner->MetaDescription ?: $config->Tagline),
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'addressLocality' => 'Šumber',
                'addressRegion' => 'Istria',
                'addressCountry' => 'HR',
                'streetAddress' => $config->Address ? trim(strtok((string) $config->Address, "\n")) : null,
            ]),
            'containedInPlace' => ['@type' => 'Place', 'name' => 'Labin, Istria, Croatia'],
            'checkinTime' => '16:00',
            'checkoutTime' => '10:00',
        ];

        if ($origin = RouteService::origin()) {
            $data['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $origin['lat'], 'longitude' => $origin['lng']];
            $data['hasMap'] = sprintf('https://www.google.com/maps/search/?api=1&query=%F,%F', $origin['lat'], $origin['lng']);
        }
        if ($config->Email) {
            $data['email'] = (string) $config->Email;
        }
        if ($config->Phone) {
            $data['telephone'] = (string) $config->Phone;
        }
        if ((int) $config->MaxGuests > 0) {
            $data['containsPlace'] = [
                '@type' => 'Accommodation',
                'occupancy' => ['@type' => 'QuantitativeValue', 'maxValue' => (int) $config->MaxGuests],
            ];
        }
        if ($image = $this->OGImageURL()) {
            $data['image'] = $image;
        }

        $sameAs = array_values(array_filter([
            $config->BookingComURL, $config->AirbnbURL, $config->InstagramURL, $config->FacebookURL,
        ]));
        if ($sameAs) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function breadcrumbSchema(): ?array
    {
        $items = [];
        $page = $this->owner;
        while ($page && $page->exists()) {
            array_unshift($items, ['name' => (string) ($page->MenuTitle ?: $page->Title), 'url' => $page->AbsoluteLink()]);
            $page = $page->ParentID ? $page->Parent() : null;
        }

        $home = SiteTree::get_by_link(RootURLController::get_homepage_link());
        if ($home && $home->ID !== $this->owner->ID) {
            array_unshift($items, ['name' => (string) ($home->MenuTitle ?: $home->Title), 'url' => Director::absoluteBaseURL()]);
        }

        if (count($items) < 2) {
            return null;
        }

        $list = [];
        foreach ($items as $i => $item) {
            $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $item['name'], 'item' => $item['url']];
        }

        return ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }

    /**
     * @return array<string, mixed>
     */
    private function articleSchema(): array
    {
        $post = $this->owner;
        $config = SiteConfig::current_site_config();
        $org = ['@type' => 'Organization', 'name' => (string) $config->Title, 'url' => Director::absoluteBaseURL()];

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => (string) ($post->MetaTitle ?: $post->Title),
            'description' => (string) $post->MetaDescription,
            'url' => $post->AbsoluteLink(),
            'mainEntityOfPage' => $post->AbsoluteLink(),
            'inLanguage' => str_replace('_', '-', i18n::get_locale()),
            'datePublished' => $post->PublishDate ? date('c', strtotime((string) $post->PublishDate)) : null,
            'dateModified' => date('c', strtotime((string) $post->LastEdited)),
            'author' => $org,
            'publisher' => $org,
        ];

        if ($image = $this->OGImageURL()) {
            $data['image'] = $image;
        }
        if ($place = $this->RelatedPlace()) {
            $data['about'] = $place->toSchema();
        }

        return array_filter($data, static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * True when the first Elemental block is a Hero → transparent header, no page header.
     */
    public function HasHero(): bool
    {
        $first = $this->firstElement();

        return $first instanceof HeroElement;
    }

    public function HasElements(): bool
    {
        return $this->firstElement() !== null;
    }

    /**
     * Last block is a full-bleed CTA → footer can sit flush below it.
     */
    public function EndsWithCTA(): bool
    {
        if (!$this->owner->hasMethod('ElementalArea')) {
            return false;
        }

        $last = $this->owner->ElementalArea()->Elements()->sort('Sort', 'DESC')->first();

        return $last instanceof CTAElement;
    }

    /**
     * Dropdown in the menu? Not for blogs (posts are children) or pages without visible children.
     */
    public function ShowSubmenu(): bool
    {
        if (is_a($this->owner, 'SilverStripe\\Blog\\Model\\Blog')) {
            return false;
        }

        return $this->owner->Children()->filter('ShowInMenus', true)->exists();
    }

    public function BodyClasses(): string
    {
        $classes = ['page-' . strtolower(ClassInfo::shortName($this->owner))];

        if ($this->HasHero()) {
            $classes[] = 'has-hero';
        }

        return implode(' ', $classes);
    }

    private function firstElement(): ?BaseElement
    {
        if (!$this->owner->hasMethod('ElementalArea')) {
            return null;
        }

        $area = $this->owner->ElementalArea();
        if (!$area || !$area->exists()) {
            return null;
        }

        /** @var BaseElement|null $first */
        $first = $area->Elements()->sort('Sort')->first();

        return $first;
    }
}

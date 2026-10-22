<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Elements\CTAElement;
use App\Elements\HeroElement;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;

/**
 * Theme helpers on every page.
 *
 * @method Image HeaderImage()
 * @extends Extension<SiteTree>
 */
class ThemePageExtension extends Extension
{
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

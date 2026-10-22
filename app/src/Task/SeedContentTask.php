<?php

declare(strict_types=1);

namespace App\Task;

use App\Booking\Model\ICalFeed;
use App\Booking\Model\Season;
use App\Booking\Page\BookingPage;
use App\Task\Seed\SeedData;
use DateTimeImmutable;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\i18n\i18n;
use SilverStripe\LinkField\Models\SiteTreeLink;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Versioned\Versioned;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use TractorCow\Fluent\Extension\FluentExtension;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

/**
 * Starter content: pages, Elemental blocks, blog, contact form, seasons,
 * settings, calendar feeds — in HR / DE / EN (Fluent).
 *
 *   vendor/bin/sake tasks:villa-seed
 *   vendor/bin/sake tasks:villa-seed --remove-defaults   # also archive installer "About Us" / "Contact Us"
 *   vendor/bin/sake tasks:villa-seed --force             # run again; only missing pages/seasons are added
 */
class SeedContentTask extends BuildTask
{
    protected static string $commandName = 'villa-seed';
    protected string $title = 'Villa Dina: seed starter content';
    protected static string $description = 'Creates pages, blocks, blog, contact form, seasons, settings and HR/DE/EN translations.';

    /** Elemental page type (your ModularPage) */
    private static string $page_class = 'App\Pages\ModularPage';

    /** Relative to the project root */
    private static string $images_path = 'app/seed/images';

    /** Fluent locales created when none exist. First = global default. */
    private static array $locales = [
        'hr_HR' => ['title' => 'Hrvatski', 'url' => 'hr'],
        'de_DE' => ['title' => 'Deutsch', 'url' => 'de'],
        'en_GB' => ['title' => 'English', 'url' => 'en'],
    ];

    private const BLOG_CLASS = 'SilverStripe\\Blog\\Model\\Blog';
    private const BLOG_POST_CLASS = 'SilverStripe\\Blog\\Model\\BlogPost';
    private const BLOG_CATEGORY_CLASS = 'SilverStripe\\Blog\\Model\\BlogCategory';
    private const FORM_CLASS = 'SilverStripe\\UserForms\\Model\\UserDefinedForm';
    private const RECIPIENT_CLASS = 'SilverStripe\\UserForms\\Model\\Recipient\\EmailRecipient';

    private PolyOutput $out;
    private bool $fluent = false;
    private string $lang = 'hr';

    /** @var array<string, Image> */
    private array $images = [];

    /** @var array<string, SiteTree> */
    private array $pages = [];

    /** @var array<string, bool> page key => created by this run */
    private array $isNew = [];

    /** @var list<SiteTree> pages to publish (incl. blog posts) */
    private array $publish = [];

    /** @var list<array{0: class-string<DataObject>, 1: int, 2: array<string, array<string, mixed>>}> */
    private array $translations = [];

    public function getOptions(): array
    {
        return [
            new InputOption('force', null, InputOption::VALUE_NONE, 'Run although content exists (adds only what is missing)'),
            new InputOption('remove-defaults', null, InputOption::VALUE_NONE, 'Archive the installer pages "About Us" and "Contact Us"'),
        ];
    }

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $this->out = $output;
        $pageClass = (string) static::config()->get('page_class');

        if (!class_exists($pageClass) || !is_a($pageClass, SiteTree::class, true)) {
            $output->writeln("<error>Page class \"{$pageClass}\" not found. Set App\\Task\\SeedContentTask.page_class.</error>");

            return Command::FAILURE;
        }

        if (!$input->getOption('force') && BookingPage::get()->exists()) {
            $output->writeln('Content already seeded (a Booking page exists). Use --force to add missing parts.');

            return Command::SUCCESS;
        }

        $this->fluent = class_exists(FluentState::class);

        return Versioned::withVersionedMode(function () use ($input): int {
            Versioned::set_stage(Versioned::DRAFT);

            $default = $this->ensureLocales();
            $this->lang = $this->langOf($default);
            $this->out->writeln("Default locale: {$default}" . ($this->fluent ? ' (Fluent)' : ''));

            $this->inLocale($default, function () use ($input): void {
                if ($input->getOption('remove-defaults')) {
                    $this->removeInstallerPages();
                }
                $this->importImages();
                $this->seedPages();
                $this->seedSiteConfig();
                $this->seedSeasons();
                $this->seedFeeds();
                $this->publishAll();
            });

            if ($this->fluent) {
                foreach (array_keys(static::config()->get('locales')) as $locale) {
                    if ($locale !== $default && Locale::getByLocale($locale)) {
                        $this->translate($locale);
                    }
                }
            }

            $this->out->writeln('<info>Done.</info> Replace all [placeholders], example prices and photos in the CMS.');

            return Command::SUCCESS;
        });
    }

    // =====================================================================
    // Locales
    // =====================================================================

    private function ensureLocales(): string
    {
        $configured = static::config()->get('locales');

        if (!$this->fluent) {
            return i18n::get_locale();
        }

        if (!Locale::get()->exists()) {
            $sort = 1;
            foreach ($configured as $code => $info) {
                Locale::create([
                    'Locale' => $code,
                    'Title' => $info['title'],
                    'URLSegment' => $info['url'],
                    'IsGlobalDefault' => $sort === 1,
                    'Sort' => $sort++,
                    'Timezone' => 'Europe/Zagreb',
                ])->write();
                $this->out->writeln("Locale created: {$code}");
            }
        }

        return Locale::getDefault()?->Locale ?? (string) array_key_first($configured);
    }

    private function inLocale(string $locale, callable $fn): mixed
    {
        if (!$this->fluent) {
            return $fn();
        }

        return FluentState::singleton()->withState(function (FluentState $state) use ($locale, $fn) {
            $state->setLocale($locale);
            $state->setIsFrontend(false);
            i18n::set_locale($locale);

            return $fn();
        });
    }

    private function langOf(string $locale): string
    {
        $lang = strtolower(substr($locale, 0, 2));

        return in_array($lang, ['hr', 'de', 'en'], true) ? $lang : 'en';
    }

    // =====================================================================
    // Content
    // =====================================================================

    private function removeInstallerPages(): void
    {
        foreach (['about-us', 'contact-us'] as $segment) {
            $page = SiteTree::get()->filter(['URLSegment' => $segment, 'ParentID' => 0, 'ClassName' => 'Page'])->first();
            if ($page && !$page->Children()->exists()) {
                $page->doArchive();
                $this->out->writeln("Archived installer page /{$segment}");
            }
        }
    }

    private function importImages(): void
    {
        $dir = Director::baseFolder() . '/' . trim((string) static::config()->get('images_path'), '/');
        $folder = Folder::find_or_make('Villa');

        foreach (SeedData::images() as $key => $info) {
            $path = $dir . '/' . $info['file'];

            $image = Image::get()->filter(['Name' => $info['file'], 'ParentID' => $folder->ID])->first();
            if (!$image) {
                if (!is_file($path)) {
                    $this->out->writeln("<comment>Image missing: {$path}</comment>");
                    continue;
                }
                $image = Image::create();
                $image->setFromLocalFile($path, 'Villa/' . $info['file']);
                $image->Title = $info['title'];
                $image->write();
                $image->publishSingle();
            }

            $this->images[$key] = $image;
        }

        $this->out->writeln(sprintf('Images: %d', count($this->images)));
    }

    private function seedPages(): void
    {
        $sort = 1;
        foreach (SeedData::pages() as $def) {
            $result = $this->ensurePage($def, $sort++);
            if (!$result) {
                continue;
            }
            [$page, $isNew] = $result;
            $this->pages[$def['key']] = $page;
            $this->isNew[$def['key']] = $isNew;
            $this->publish[] = $page;

            $this->out->writeln(sprintf('%s page "%s" (%s)', $isNew ? 'Created' : 'Kept', $page->Title, $page->ClassName));
        }

        // Second pass: blocks/children need all pages to exist (links)
        foreach (SeedData::pages() as $def) {
            $page = $this->pages[$def['key']] ?? null;
            if (!$page || empty($this->isNew[$def['key']])) {
                continue;
            }

            if (!empty($def['elements'])) {
                $this->buildElements($page, SeedData::elements((string) $def['elements']));
            }
            if ($def['type'] === 'blog') {
                $this->seedBlogPosts($page);
            }
            if ($def['type'] === 'form') {
                $this->seedContactForm($page);
            }
        }

        if (isset($this->pages['booking'], $this->pages['terms'])) {
            $booking = $this->pages['booking'];
            $booking->TermsPageID = $this->pages['terms']->ID;
            $booking->write();
        }
    }

    /**
     * @param array<string, mixed> $def
     * @return array{0: SiteTree, 1: bool}|null
     */
    private function ensurePage(array $def, int $sort): ?array
    {
        $class = match ($def['type']) {
            'modular' => (string) static::config()->get('page_class'),
            'booking' => BookingPage::class,
            'blog' => self::BLOG_CLASS,
            'form' => self::FORM_CLASS,
            default => 'Page',
        };
        if (!class_exists($class)) {
            $this->out->writeln("<comment>Skipped \"{$def['key']}\": {$class} not installed</comment>");

            return null;
        }

        $segment = (string) $this->tr($def['URLSegment']);
        $existing = SiteTree::get()->filter(['URLSegment' => $segment, 'ParentID' => 0])->first();

        $fields = array_filter([
            'Title' => $def['Title'],
            'MenuTitle' => $def['MenuTitle'] ?? null,
            'URLSegment' => $def['URLSegment'],
            'Content' => $def['Content'] ?? null,
            'MetaDescription' => $def['MetaDescription'] ?? null,
        ], static fn ($v) => $v !== null);
        $fields['ShowInMenus'] = $def['ShowInMenus'] ?? true;
        $fields['ShowInSearch'] = true;
        $fields['Sort'] = $sort;
        if (!empty($def['header']) && isset($this->images[$def['header']])) {
            $fields['HeaderImageID'] = $this->images[$def['header']]->ID;
        }
        if ($def['type'] === 'form') {
            $fields['SubmitButtonText'] = ['hr' => 'Pošalji', 'de' => 'Senden', 'en' => 'Send'];
            $fields['OnCompleteMessage'] = [
                'hr' => '<h2>Hvala!</h2><p>Vaša poruka je poslana. Javit ćemo vam se uskoro.</p>',
                'de' => '<h2>Vielen Dank!</h2><p>Ihre Nachricht wurde gesendet. Wir melden uns in Kürze.</p>',
                'en' => '<h2>Thank you!</h2><p>Your message has been sent. We will be in touch shortly.</p>',
            ];
        }

        if ($existing) {
            // Installer home page → convert to the Elemental page type
            $hasBlocks = $existing->hasMethod('ElementalArea') && $existing->ElementalArea()->Elements()->exists();
            if ($def['key'] === 'home' && !$hasBlocks) {
                if (!$existing instanceof $class) {
                    $existing = $existing->newClassInstance($class);
                }
                $this->apply($existing, $fields);

                return [$existing, true];
            }

            return [$existing, false];
        }

        /** @var SiteTree $page */
        $page = $this->createRecord($class, $fields + ['ParentID' => 0]);

        return [$page, true];
    }

    /**
     * @param list<array<string, mixed>> $defs
     */
    private function buildElements(SiteTree $page, array $defs): void
    {
        $page->write(); // ensures the ElementalArea exists
        $area = $page->ElementalArea();
        $sort = 1;

        foreach ($defs as $def) {
            $class = $def['class'];
            if (!class_exists($class) || !is_a($class, BaseElement::class, true)) {
                $this->out->writeln("<comment>  Block {$class} not available – skipped</comment>");
                continue;
            }

            $fields = $def['fields'] + ['ParentID' => $area->ID, 'Sort' => $sort++];
            if (!empty($def['style'])) {
                $fields['Style'] = $def['style'];
            }
            foreach ($def['hasOne'] ?? [] as $relation => $imageKey) {
                $fields[$relation . 'ID'] = $this->images[$imageKey]->ID ?? 0;
            }

            /** @var BaseElement $element */
            $element = $this->createRecord($class, $fields);

            foreach ($def['slides'] ?? [] as $relation => $keys) {
                $this->attachImages($element, $relation, $keys);
            }
            if (!empty($def['gallery'])) {
                $this->attachImages($element, 'Images', $def['gallery']);
            }

            foreach ($def['links'] ?? [] as $relation => [$pageKey, $text]) {
                $target = $this->pages[$pageKey] ?? null;
                if (!$target || !class_exists(SiteTreeLink::class)) {
                    continue;
                }
                $link = $this->createRecord(SiteTreeLink::class, [
                    'PageID' => $target->ID,
                    'LinkText' => $text,
                    'OwnerID' => $element->ID,
                    'OwnerClass' => $element->ClassName,
                    'OwnerRelation' => $relation,
                ]);
                $element->setField($relation . 'ID', $link->ID);
            }

            foreach ($def['items'] ?? [] as $rows) {
                [$itemClass, $list] = $rows;
                foreach ($list as $i => $row) {
                    $this->createRecord($itemClass, $row + ['ParentID' => $element->ID, 'Sort' => $i + 1]);
                }
            }

            $element->write();
            $this->out->writeln('  + ' . $element->getType());
        }
    }

    /**
     * @param list<string> $keys
     */
    private function attachImages(DataObject $owner, string $relation, array $keys): void
    {
        if (!$owner->hasMethod($relation)) {
            $this->out->writeln("<comment>  {$owner->ClassName} has no relation {$relation}</comment>");

            return;
        }
        $list = $owner->$relation();
        foreach (array_values($keys) as $i => $key) {
            if (isset($this->images[$key])) {
                $list->add($this->images[$key], ['SortOrder' => $i + 1]);
            }
        }
    }

    private function seedBlogPosts(SiteTree $blog): void
    {
        if (!class_exists(self::BLOG_POST_CLASS)) {
            return;
        }

        $categories = [];
        foreach (SeedData::blogPosts() as $i => $def) {
            $key = (string) $this->tr($def['category']);
            $categories[$key] ??= $this->createRecord(self::BLOG_CATEGORY_CLASS, ['BlogID' => $blog->ID, 'Title' => $def['category']]);

            $post = $this->createRecord(self::BLOG_POST_CLASS, [
                'ParentID' => $blog->ID,
                'Title' => $def['Title'],
                'URLSegment' => $def['URLSegment'],
                'Summary' => $def['Summary'],
                'Content' => $def['Content'],
                'PublishDate' => date('Y-m-d H:i:s', DBDatetime::now()->getTimestamp() - ($i + 1) * 86400 * 7),
                'FeaturedImageID' => $this->images[$def['image']]->ID ?? 0,
                'ShowInMenus' => false,
            ]);
            $post->Categories()->add($categories[$key]);
            $this->publish[] = $post;
            $this->out->writeln('  + post "' . $post->Title . '"');
        }
    }

    private function seedContactForm(SiteTree $form): void
    {
        $sort = 2;
        foreach (SeedData::contactFields() as $def) {
            $class = $def['class'];
            unset($def['class']);
            $this->createRecord($class, $def + [
                'ParentID' => $form->ID,
                'ParentClass' => $form->ClassName,
                'Sort' => $sort++,
            ]);
        }
        if ($form->hasMethod('createInitialFormStep')) {
            $form->createInitialFormStep(true);
        }

        $email = (string) (SiteConfig::current_site_config()->Email ?: SeedData::siteConfig()['Email']);
        if (class_exists(self::RECIPIENT_CLASS)) {
            $this->createRecord(self::RECIPIENT_CLASS, [
                'FormID' => $form->ID,
                'FormClass' => $form->ClassName,
                'EmailAddress' => $email,
                'EmailFrom' => $email,
                'EmailSubject' => 'Kontakt – Villa Dina',
                'EmailBodyHtml' => '<p>New contact form submission:</p>',
            ]);
        }
        $this->out->writeln('  + contact form fields');
    }

    private function seedSiteConfig(): void
    {
        $config = SiteConfig::current_site_config();
        $data = SeedData::siteConfig();
        if ($config->Email) {
            unset($data['Email']);
        }

        foreach (['InquiryPageID' => 'booking', 'ImprintPageID' => 'imprint', 'PrivacyPageID' => 'privacy'] as $field => $key) {
            if (isset($this->pages[$key])) {
                $data[$field] = $this->pages[$key]->ID;
            }
        }

        $this->apply($config, $data);
        $this->out->writeln('Settings updated');
    }

    private function seedSeasons(): void
    {
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');
        $year = (int) date('Y');
        $count = 0;

        foreach ([$year, $year + 1] as $y) {
            foreach (SeedData::seasons($y) as $def) {
                if ($def['EndDate'] < $today) {
                    continue;
                }
                $overlap = Season::get()->filter([
                    'StartDate:LessThanOrEqual' => $def['EndDate'],
                    'EndDate:GreaterThanOrEqual' => $def['StartDate'],
                ])->exists();
                if ($overlap) {
                    continue;
                }
                $this->createRecord(Season::class, $def);
                $count++;
            }
        }

        $this->out->writeln("Seasons created: {$count} (example prices)");
    }

    private function seedFeeds(): void
    {
        if (ICalFeed::get()->exists()) {
            return;
        }
        foreach (['booking' => 'Booking.com', 'airbnb' => 'Airbnb'] as $source => $title) {
            ICalFeed::create(['Title' => $title, 'Source' => $source, 'Active' => true])->write();
        }
        $this->out->writeln('Calendar feeds created (add import URLs in Bookings → Calendar sync)');
    }

    private function publishAll(): void
    {
        foreach ($this->publish as $page) {
            $page->publishRecursive();
        }
        $this->out->writeln(sprintf('Published %d pages', count($this->publish)));
    }

    // =====================================================================
    // Translations
    // =====================================================================

    private function translate(string $locale): void
    {
        $lang = $this->langOf($locale);

        $this->inLocale($locale, function () use ($lang, $locale): void {
            $done = 0;
            foreach ($this->translations as [$class, $id, $fields]) {
                $record = DataObject::get($class)->byID($id);
                if (!$record || !$this->isLocalised($record)) {
                    continue;
                }
                foreach ($fields as $field => $values) {
                    $record->setField($field, $values[$lang] ?? reset($values));
                }
                $record->write();
                $done++;
            }

            foreach ($this->publish as $page) {
                $localised = SiteTree::get()->byID($page->ID);
                $localised?->publishRecursive();
            }

            $this->out->writeln("Translated {$done} records → {$locale}");
        });
    }

    private function isLocalised(DataObject $record): bool
    {
        foreach ($record->getExtensionInstances() as $extension) {
            if ($extension instanceof FluentExtension) {
                return true;
            }
        }

        return false;
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * Create + write a record; translatable values (['hr'=>…,'de'=>…,'en'=>…]) are
     * written in the default language and queued for the other locales.
     *
     * @param class-string<DataObject> $class
     * @param array<string, mixed> $data
     */
    private function createRecord(string $class, array $data): DataObject
    {
        /** @var DataObject $record */
        $record = $class::create();

        return $this->apply($record, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function apply(DataObject $record, array $data): DataObject
    {
        $i18n = [];
        foreach ($data as $field => $value) {
            if ($this->isTranslatable($value)) {
                $i18n[$field] = $value;
                $value = $this->tr($value);
            }
            $record->setField($field, $value);
        }
        $record->write();

        if ($i18n) {
            $this->translations[] = [$record->ClassName, (int) $record->ID, $i18n];
        }

        return $record;
    }

    private function isTranslatable(mixed $value): bool
    {
        return is_array($value) && $value !== [] && array_diff(array_keys($value), ['hr', 'de', 'en']) === [];
    }

    private function tr(mixed $value): mixed
    {
        if (!$this->isTranslatable($value)) {
            return $value;
        }

        return $value[$this->lang] ?? $value['en'] ?? reset($value);
    }
}

<?php

declare(strict_types=1);

namespace App\Elements\Items;

use App\Elements\PlacesElement;
use App\Elements\Traits\ElementItemPermissions;
use App\Geo\RouteFromVillaExtension;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataObject;

/**
 * Beach, town or sight with coordinates and driving distance from the villa.
 *
 * @property string $Title
 * @property string $Kind
 * @property string|null $Summary
 * @property string|null $Surface
 * @property string|null $Features
 * @property bool $BlueFlag
 * @property int $Sort
 * @method PlacesElement Parent()
 * @method Image Image()
 * @method SiteTree Article()
 * @mixin RouteFromVillaExtension
 */
class Place extends DataObject
{
    use ElementItemPermissions;

    private static string $table_name = 'VD_Place';
    private static string $singular_name = 'Place';
    private static string $plural_name = 'Places';

    private static array $db = [
        'Title' => 'Varchar(120)',
        'Kind' => "Enum('beach,town,nature,sight','sight')",
        'Summary' => 'Text',
        'Surface' => 'Varchar(80)',
        'Features' => 'Varchar(255)',
        'BlueFlag' => 'Boolean',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Parent' => PlacesElement::class,
        'Image' => Image::class,
        'Article' => SiteTree::class,
    ];

    private static array $owns = ['Image'];

    private static array $extensions = [
        RouteFromVillaExtension::class,
    ];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'Title' => 'Name',
        'KindLabel' => 'Type',
        'DriveLabel' => 'From the villa',
    ];

    /** schema.org type per kind */
    private static array $schema_types = [
        'beach' => 'Beach',
        'town' => 'City',
        'nature' => 'Park',
        'sight' => 'TouristAttraction',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Sort', 'ParentID', 'ArticleID']);

        $fields->replaceField('Kind', DropdownField::create('Kind', 'Type', self::kindOptions()));
        $fields->replaceField('Summary', TextareaField::create('Summary', 'Short description')
            ->setRows(4)
            ->setDescription('2–3 sentences. Answer first: what it is, why go, who it suits.'));
        $fields->dataFieldByName('Surface')?->setDescription('Beaches: e.g. pebble, rocks, sand & pebble');
        $fields->dataFieldByName('Features')?->setDescription('Comma separated, e.g. "Family friendly, Parking, Beach bar"');
        $fields->replaceField('BlueFlag', CheckboxField::create('BlueFlag', 'Blue Flag beach'));
        $fields->replaceField('Image', UploadField::create('Image', 'Image')
            ->setFolderName('Places')
            ->setAllowedFileCategories('image/supported'));
        $fields->insertAfter('Features', TreeDropdownField::create('ArticleID', 'Linked article / guide', SiteTree::class)
            ->setDescription('Optional – shows a "Read more" link and the distance box on that page.'));

        return $fields;
    }

    /**
     * @return array<string, string>
     */
    public static function kindOptions(): array
    {
        return [
            'beach' => _t(__CLASS__ . '.KindBeach', 'Beach'),
            'town' => _t(__CLASS__ . '.KindTown', 'Town'),
            'nature' => _t(__CLASS__ . '.KindNature', 'Nature'),
            'sight' => _t(__CLASS__ . '.KindSight', 'Sight'),
        ];
    }

    public function KindLabel(): string
    {
        return self::kindOptions()[$this->Kind] ?? (string) $this->Kind;
    }

    public function FeatureList(): ArrayList
    {
        $items = array_filter(array_map('trim', explode(',', (string) $this->Features)));

        return ArrayList::create(array_map(static fn (string $f) => ArrayData::create(['Title' => $f]), array_values($items)));
    }

    public function Anchor(): string
    {
        return 'place-' . $this->ID;
    }

    /**
     * schema.org entity for ItemList / BlogPosting.about
     *
     * @return array<string, mixed>
     */
    public function toSchema(): array
    {
        $types = (array) static::config()->get('schema_types');
        $data = [
            '@type' => $types[$this->Kind] ?? 'Place',
            'name' => (string) $this->Title,
        ];

        if ($this->Summary) {
            $data['description'] = (string) $this->Summary;
        }
        if ($geo = $this->GeoSchema()) {
            $data['geo'] = $geo;
            $data['hasMap'] = $this->MapURL();
        }
        if ($this->Article()->exists()) {
            $data['url'] = $this->Article()->AbsoluteLink();
        }
        if ($this->Image()->exists()) {
            $data['image'] = $this->Image()->getAbsoluteURL();
        }
        $data['address'] = ['@type' => 'PostalAddress', 'addressRegion' => 'Istria', 'addressCountry' => 'HR'];

        return $data;
    }
}

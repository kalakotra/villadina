<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Items\Distance;
use App\Elements\Traits\FormatsHeading;
use App\Elements\Traits\HasItemsGrid;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\HasManyList;

/**
 * Location text, distance list and a consent-gated map (OSM or Google).
 *
 * @property string|null $Eyebrow
 * @property string|null $Content
 * @property float $Latitude
 * @property float $Longitude
 * @property int $MapZoom
 * @property string $MapProvider
 * @method HasManyList<Distance> Distances()
 */
class LocationElement extends BaseElement
{
    use FormatsHeading;
    use HasItemsGrid;

    private static string $table_name = 'VD_LocationElement';
    private static string $singular_name = 'Location';
    private static string $plural_name = 'Locations';
    private static string $description = 'Map + distances to beach, town, airport';
    private static string $icon = 'font-icon-globe';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Content' => 'HTMLText',
        'Latitude' => 'Decimal(9,6)',
        'Longitude' => 'Decimal(9,6)',
        'MapZoom' => 'Int',
        'MapProvider' => "Enum('osm,google','osm')",
    ];

    private static array $has_many = [
        'Distances' => Distance::class,
    ];

    private static array $owns = ['Distances'];
    private static array $cascade_deletes = ['Distances'];
    private static array $cascade_duplicates = ['Distances'];

    private static array $defaults = [
        'ShowTitle' => true,
        'MapZoom' => 12,
        'MapProvider' => 'osm',
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName('Distances');
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                HTMLEditorField::create('Content', 'Text')->setRows(8),
                NumericField::create('Latitude', 'Latitude')->setScale(6),
                NumericField::create('Longitude', 'Longitude')->setScale(6),
                NumericField::create('MapZoom', 'Zoom (1–19)'),
                DropdownField::create('MapProvider', 'Map provider', [
                    'osm' => 'OpenStreetMap',
                    'google' => 'Google Maps',
                ]),
                $this->itemsGrid('Distances', 'Distances'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Location';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    public function SortedDistances(): HasManyList
    {
        return $this->Distances()->sort('Sort');
    }

    public function HasCoordinates(): bool
    {
        return (float) $this->Latitude !== 0.0 && (float) $this->Longitude !== 0.0;
    }

    /**
     * Embed URL — only requested after visitor consent (see js/modules/map.js).
     */
    public function MapEmbedURL(): string
    {
        if (!$this->HasCoordinates()) {
            return '';
        }

        $lat = (float) $this->Latitude;
        $lng = (float) $this->Longitude;
        $zoom = max(1, min(19, (int) $this->MapZoom ?: 12));

        if ($this->MapProvider === 'google') {
            return sprintf('https://maps.google.com/maps?q=%F,%F&z=%d&output=embed', $lat, $lng, $zoom);
        }

        // Approximate bbox for the zoom level
        $d = 360 / (2 ** $zoom) / 2;

        return sprintf(
            'https://www.openstreetmap.org/export/embed.html?bbox=%F,%F,%F,%F&layer=mapnik&marker=%F,%F',
            $lng - $d,
            $lat - $d / 2,
            $lng + $d,
            $lat + $d / 2,
            $lat,
            $lng,
        );
    }

    public function DirectionsURL(): string
    {
        return $this->HasCoordinates()
            ? sprintf('https://www.google.com/maps/dir/?api=1&destination=%F,%F', (float) $this->Latitude, (float) $this->Longitude)
            : '';
    }
}

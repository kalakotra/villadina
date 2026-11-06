<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Items\Place;
use App\Elements\Traits\FormatsHeading;
use App\Elements\Traits\HasItemsGrid;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\List\SS_List;
use SilverStripe\ORM\HasManyList;

/**
 * List of beaches / destinations with driving distance from the villa
 * and schema.org ItemList structured data.
 *
 * @property string|null $Eyebrow
 * @property string|null $Content
 * @property string $SortBy
 * @property bool $OutputSchema
 * @method HasManyList<Place> Places()
 */
class PlacesElement extends BaseElement
{
    use FormatsHeading;
    use HasItemsGrid;

    private static string $table_name = 'VD_PlacesElement';
    private static string $singular_name = 'Places (beaches / destinations)';
    private static string $plural_name = 'Places';
    private static string $description = 'Beaches or day trips with distance from the villa';
    private static string $icon = 'font-icon-p-map';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Content' => 'HTMLText',
        'SortBy' => "Enum('manual,distance','manual')",
        'OutputSchema' => 'Boolean',
    ];

    private static array $has_many = [
        'Places' => Place::class,
    ];

    private static array $owns = ['Places'];
    private static array $cascade_deletes = ['Places'];
    private static array $cascade_duplicates = ['Places'];

    private static array $defaults = [
        'ShowTitle' => true,
        'SortBy' => 'manual',
        'OutputSchema' => true,
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName('Places');
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                HTMLEditorField::create('Content', 'Intro')->setRows(6),
                DropdownField::create('SortBy', 'Order', [
                    'manual' => 'Manual (drag & drop)',
                    'distance' => 'Nearest first (driving time)',
                ]),
                CheckboxField::create('OutputSchema', 'Output ItemList structured data (SEO)'),
                $this->itemsGrid('Places', 'Places'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Places';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    /**
     * Manual order, or nearest first (driving minutes, then straight-line km; unknown last).
     */
    public function SortedPlaces(): SS_List
    {
        $list = $this->Places()->sort('Sort');
        if ($this->SortBy !== 'distance') {
            return $list;
        }

        $items = $list->toArray();
        usort($items, static function (Place $a, Place $b): int {
            $key = static fn (Place $p): array => [
                (int) $p->DriveMinutes > 0 ? 0 : ((float) $p->StraightKm > 0 ? 1 : 2),
                (int) $p->DriveMinutes ?: (float) $p->StraightKm,
                (int) $p->Sort,
            ];

            return $key($a) <=> $key($b);
        });

        return ArrayList::create($items);
    }

    public function SchemaJSON(): ?DBHTMLText
    {
        if (!$this->OutputSchema || !$this->Places()->exists()) {
            return null;
        }

        $items = [];
        $position = 1;
        foreach ($this->SortedPlaces() as $place) {
            $items[] = ['@type' => 'ListItem', 'position' => $position++, 'item' => $place->toSchema()];
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $this->TitlePlain(),
            'numberOfItems' => count($items),
            'itemListElement' => $items,
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);

        return DBField::create_field('HTMLFragment', '<script type="application/ld+json">' . $json . '</script>');
    }
}

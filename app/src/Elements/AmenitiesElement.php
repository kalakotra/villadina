<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Items\Amenity;
use App\Elements\Traits\FormatsHeading;
use App\Elements\Traits\HasItemsGrid;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\HasManyList;

/**
 * @property string|null $Eyebrow
 * @property string|null $Content
 * @method HasManyList<Amenity> Amenities()
 */
class AmenitiesElement extends BaseElement
{
    use FormatsHeading;
    use HasItemsGrid;

    private static string $table_name = 'VD_AmenitiesElement';
    private static string $singular_name = 'Amenities';
    private static string $plural_name = 'Amenities';
    private static string $description = 'Icon grid of villa amenities';
    private static string $icon = 'font-icon-check-mark-2';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Content' => 'HTMLText',
    ];

    private static array $has_many = [
        'Amenities' => Amenity::class,
    ];

    private static array $owns = ['Amenities'];
    private static array $cascade_deletes = ['Amenities'];
    private static array $cascade_duplicates = ['Amenities'];

    private static array $defaults = [
        'ShowTitle' => true,
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName('Amenities');
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                HTMLEditorField::create('Content', 'Intro')->setRows(5),
                $this->itemsGrid('Amenities', 'Amenities'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Amenities';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    public function SortedAmenities(): HasManyList
    {
        return $this->Amenities()->sort('Sort');
    }
}

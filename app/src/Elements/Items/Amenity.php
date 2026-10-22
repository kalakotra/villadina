<?php

declare(strict_types=1);

namespace App\Elements\Items;

use App\Elements\AmenitiesElement;
use App\Elements\Traits\ElementItemPermissions;
use App\View\Icons;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * @property string $Title
 * @property string|null $Description
 * @property string|null $IconKey
 * @property int $Sort
 * @method AmenitiesElement Parent()
 * @method File CustomIcon()
 */
class Amenity extends DataObject
{
    use ElementItemPermissions;

    private static string $table_name = 'VD_Amenity';
    private static string $singular_name = 'Amenity';
    private static string $plural_name = 'Amenities';

    private static array $db = [
        'Title' => 'Varchar(120)',
        'Description' => 'Text',
        'IconKey' => 'Varchar(30)',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Parent' => AmenitiesElement::class,
        'CustomIcon' => File::class,
    ];

    private static array $owns = ['CustomIcon'];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'IconKey' => 'Icon',
        'Title' => 'Title',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Sort', 'ParentID', 'CustomIconID']);

        $fields->replaceField(
            'IconKey',
            DropdownField::create('IconKey', 'Icon', Icons::amenityOptions())->setEmptyString('— none —'),
        );

        $fields->addFieldToTab(
            'Root.Main',
            UploadField::create('CustomIcon', 'Custom icon (overrides the above)')
                ->setFolderName('Icons')
                ->setAllowedExtensions(['svg', 'png', 'webp']),
        );

        return $fields;
    }

    public function IconHTML(): ?DBHTMLText
    {
        return $this->IconKey ? Icons::svg((string) $this->IconKey) : null;
    }
}

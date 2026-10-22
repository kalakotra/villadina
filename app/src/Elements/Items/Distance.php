<?php

declare(strict_types=1);

namespace App\Elements\Items;

use App\Elements\LocationElement;
use App\Elements\Traits\ElementItemPermissions;
use App\View\Icons;
use SilverStripe\Forms\DropdownField;
use SilverStripe\ORM\DataObject;

/**
 * @property string $Title
 * @property string $Value
 * @property string|null $IconKey
 * @property int $Sort
 * @method LocationElement Parent()
 */
class Distance extends DataObject
{
    use ElementItemPermissions;

    private static string $table_name = 'VD_Distance';
    private static string $singular_name = 'Distance';
    private static string $plural_name = 'Distances';

    private static array $db = [
        'Title' => 'Varchar(120)',
        'Value' => 'Varchar(40)',
        'IconKey' => 'Varchar(30)',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Parent' => LocationElement::class,
    ];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'Title' => 'Place',
        'Value' => 'Distance',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Sort', 'ParentID']);
        $fields->dataFieldByName('Title')?->setDescription('e.g. Beach Rabac, Labin old town, Pula airport');
        $fields->dataFieldByName('Value')?->setDescription('e.g. 6 km, 10 min, 45 min');
        $fields->replaceField(
            'IconKey',
            DropdownField::create('IconKey', 'Icon', Icons::amenityOptions())->setEmptyString('— none —'),
        );

        return $fields;
    }
}

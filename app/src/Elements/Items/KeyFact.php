<?php

declare(strict_types=1);

namespace App\Elements\Items;

use App\Elements\KeyFactsElement;
use App\Elements\Traits\ElementItemPermissions;
use SilverStripe\ORM\DataObject;

/**
 * @property string $Value
 * @property string $Label
 * @property int $Sort
 * @method KeyFactsElement Parent()
 */
class KeyFact extends DataObject
{
    use ElementItemPermissions;

    private static string $table_name = 'VD_KeyFact';
    private static string $singular_name = 'Fact';
    private static string $plural_name = 'Facts';

    private static array $db = [
        'Value' => 'Varchar(20)',
        'Label' => 'Varchar(80)',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Parent' => KeyFactsElement::class,
    ];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'Value' => 'Value',
        'Label' => 'Label',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Sort', 'ParentID']);
        $fields->dataFieldByName('Value')?->setDescription('e.g. 8, 4, 32 m², 3 km');

        return $fields;
    }

    public function getTitle(): string
    {
        return trim($this->Value . ' ' . $this->Label);
    }
}

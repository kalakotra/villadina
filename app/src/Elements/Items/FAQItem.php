<?php

declare(strict_types=1);

namespace App\Elements\Items;

use App\Elements\FAQElement;
use App\Elements\Traits\ElementItemPermissions;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\ORM\DataObject;

/**
 * @property string $Question
 * @property string|null $Answer
 * @property int $Sort
 * @method FAQElement Parent()
 */
class FAQItem extends DataObject
{
    use ElementItemPermissions;

    private static string $table_name = 'VD_FAQItem';
    private static string $singular_name = 'Question';
    private static string $plural_name = 'Questions';

    private static array $db = [
        'Question' => 'Varchar(255)',
        'Answer' => 'HTMLText',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Parent' => FAQElement::class,
    ];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'Question' => 'Question',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Sort', 'ParentID']);
        $fields->replaceField('Answer', HTMLEditorField::create('Answer', 'Answer')->setRows(8));

        return $fields;
    }

    public function getTitle(): string
    {
        return (string) $this->Question;
    }
}

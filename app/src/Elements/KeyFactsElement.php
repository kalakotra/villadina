<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Items\KeyFact;
use App\Elements\Traits\FormatsHeading;
use App\Elements\Traits\HasItemsGrid;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\HasManyList;

/**
 * Row of big numbers: guests, bedrooms, bathrooms, pool size, distance to sea …
 *
 * @property string|null $Eyebrow
 * @property string|null $Content
 * @method HasManyList<KeyFact> Facts()
 */
class KeyFactsElement extends BaseElement
{
    use FormatsHeading;
    use HasItemsGrid;

    private static string $table_name = 'VD_KeyFactsElement';
    private static string $singular_name = 'Key facts';
    private static string $plural_name = 'Key facts';
    private static string $description = 'Numbers at a glance (guests, bedrooms, pool …)';
    private static string $icon = 'font-icon-chart-line';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Content' => 'HTMLText',
    ];

    private static array $has_many = [
        'Facts' => KeyFact::class,
    ];

    private static array $owns = ['Facts'];
    private static array $cascade_deletes = ['Facts'];
    private static array $cascade_duplicates = ['Facts'];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName('Facts');
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                HTMLEditorField::create('Content', 'Intro')->setRows(5),
                $this->itemsGrid('Facts', 'Facts'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Key facts';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    public function SortedFacts(): HasManyList
    {
        return $this->Facts()->sort('Sort');
    }
}

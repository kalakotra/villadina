<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Items\FAQItem;
use App\Elements\Traits\FormatsHeading;
use App\Elements\Traits\HasItemsGrid;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\HasManyList;

/**
 * Accordion FAQ with optional FAQPage JSON-LD.
 *
 * @property string|null $Eyebrow
 * @property string|null $Content
 * @property bool $OutputSchema
 * @method HasManyList<FAQItem> Items()
 */
class FAQElement extends BaseElement
{
    use FormatsHeading;
    use HasItemsGrid;

    private static string $table_name = 'VD_FAQElement';
    private static string $singular_name = 'FAQ';
    private static string $plural_name = 'FAQs';
    private static string $description = 'Questions & answers (accordion)';
    private static string $icon = 'font-icon-help-circled';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Content' => 'HTMLText',
        'OutputSchema' => 'Boolean',
    ];

    private static array $has_many = [
        'Items' => FAQItem::class,
    ];

    private static array $owns = ['Items'];
    private static array $cascade_deletes = ['Items'];
    private static array $cascade_duplicates = ['Items'];

    private static array $defaults = [
        'ShowTitle' => true,
        'OutputSchema' => true,
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName('Items');
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                HTMLEditorField::create('Content', 'Intro')->setRows(5),
                CheckboxField::create('OutputSchema', 'Output FAQPage structured data (SEO)'),
                $this->itemsGrid('Items', 'Questions'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'FAQ';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    public function SortedItems(): HasManyList
    {
        return $this->Items()->sort('Sort');
    }

    public function SchemaJSON(): ?DBHTMLText
    {
        if (!$this->OutputSchema || !$this->Items()->exists()) {
            return null;
        }

        $entities = [];
        foreach ($this->SortedItems() as $item) {
            $entities[] = [
                '@type' => 'Question',
                'name' => (string) $item->Question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags((string) $item->Answer, '<p><br><a><strong><em><ul><ol><li>'),
                ],
            ];
        }

        $json = json_encode(
            ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $entities],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG,
        );

        return DBField::create_field('HTMLFragment', '<script type="application/ld+json">' . $json . '</script>');
    }
}

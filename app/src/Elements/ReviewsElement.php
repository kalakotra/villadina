<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Items\Review;
use App\Elements\Traits\FormatsHeading;
use App\Elements\Traits\HasItemsGrid;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\HasManyList;

/**
 * Guest reviews slider with optional overall score (e.g. "9.6 Booking.com").
 *
 * @property string|null $Eyebrow
 * @property string|null $Lead
 * @property float $Score
 * @property string|null $ScoreLabel
 * @method HasManyList<Review> Reviews()
 */
class ReviewsElement extends BaseElement
{
    use FormatsHeading;
    use HasItemsGrid;

    private static string $table_name = 'VD_ReviewsElement';
    private static string $singular_name = 'Reviews';
    private static string $plural_name = 'Reviews';
    private static string $description = 'Guest reviews slider';
    private static string $icon = 'font-icon-comment';
    private static bool $inline_editable = false;

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Lead' => 'Text',
        'Score' => 'Decimal(3,1)',
        'ScoreLabel' => 'Varchar(120)',
    ];

    private static array $has_many = [
        'Reviews' => Review::class,
    ];

    private static array $owns = ['Reviews'];
    private static array $cascade_deletes = ['Reviews'];
    private static array $cascade_duplicates = ['Reviews'];

    private static array $defaults = [
        'ShowTitle' => true,
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->removeByName('Reviews');
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                TextareaField::create('Lead', 'Lead')->setRows(3),
                NumericField::create('Score', 'Overall score (optional)')->setScale(1)
                    ->setDescription('e.g. 9.6 (Booking.com scale) or 4.9 (Airbnb/Google)'),
                TextField::create('ScoreLabel', 'Score label')
                    ->setDescription('e.g. "Exceptional · 48 reviews on Booking.com"'),
                $this->itemsGrid('Reviews', 'Reviews'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Reviews';
    }

    public function IsFlush(): bool
    {
        return false;
    }

    public function SortedReviews(): HasManyList
    {
        return $this->Reviews()->sort('Sort');
    }

    public function HasScore(): bool
    {
        return (float) $this->Score > 0;
    }
}

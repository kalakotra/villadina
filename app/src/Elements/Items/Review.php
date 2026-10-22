<?php

declare(strict_types=1);

namespace App\Elements\Items;

use App\Elements\ReviewsElement;
use App\Elements\Traits\ElementItemPermissions;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataObject;

/**
 * @property string $Author
 * @property string|null $Origin
 * @property string $Quote
 * @property int $Rating
 * @property string $Source
 * @property string|null $StayDate
 * @property int $Sort
 * @method ReviewsElement Parent()
 */
class Review extends DataObject
{
    use ElementItemPermissions;

    private static string $table_name = 'VD_Review';
    private static string $singular_name = 'Review';
    private static string $plural_name = 'Reviews';

    private static array $db = [
        'Author' => 'Varchar(120)',
        'Origin' => 'Varchar(120)',
        'Quote' => 'Text',
        'Rating' => 'Int',
        'Source' => "Enum('direct,booking,airbnb,google','direct')",
        'StayDate' => 'Date',
        'Sort' => 'Int',
    ];

    private static array $has_one = [
        'Parent' => ReviewsElement::class,
    ];

    private static array $defaults = [
        'Rating' => 5,
        'Source' => 'direct',
    ];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'Author' => 'Author',
        'SourceLabel' => 'Source',
        'Rating' => 'Stars',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['Sort', 'ParentID']);
        $fields->dataFieldByName('Origin')?->setDescription('e.g. "Familie aus München"');
        $fields->replaceField('Rating', DropdownField::create('Rating', 'Stars', [5 => '5', 4 => '4', 3 => '3']));
        $fields->replaceField('Source', DropdownField::create('Source', 'Source', self::sourceLabels()));

        return $fields;
    }

    public function getTitle(): string
    {
        return (string) $this->Author;
    }

    /**
     * @return array<string, string>
     */
    public static function sourceLabels(): array
    {
        return [
            'direct' => 'Direct',
            'booking' => 'Booking.com',
            'airbnb' => 'Airbnb',
            'google' => 'Google',
        ];
    }

    public function SourceLabel(): string
    {
        return self::sourceLabels()[$this->Source] ?? '';
    }

    /**
     * For <% loop $Stars %> in templates.
     */
    public function Stars(): ArrayList
    {
        return ArrayList::create(array_fill(0, max(0, min(5, (int) $this->Rating)), []));
    }
}

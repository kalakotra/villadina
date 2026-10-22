<?php

declare(strict_types=1);

namespace App\Elements;

use App\Elements\Traits\FormatsHeading;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;

/**
 * Availability / booking section.
 *
 * Phase 1 (now): date-range + guests widget → forwards to the inquiry page,
 *                optional Booking.com / Airbnb buttons.
 * Phase 2:       same element, widget reads real availability & prices from
 *                App\Booking\AvailabilityProviderInterface.
 *
 * @property string|null $Eyebrow
 * @property string|null $Content
 * @property string|null $Note
 * @property bool $ShowChannels
 */
class BookingElement extends BaseElement
{
    use FormatsHeading;

    private static string $table_name = 'VD_BookingElement';
    private static string $singular_name = 'Booking / availability';
    private static string $plural_name = 'Booking / availability';
    private static string $description = 'Availability widget and channel links';
    private static string $icon = 'font-icon-calendar';

    private static array $db = [
        'Eyebrow' => 'Varchar(120)',
        'Content' => 'HTMLText',
        'Note' => 'Text',
        'ShowChannels' => 'Boolean',
    ];

    private static array $defaults = [
        'ShowTitle' => true,
        'ShowChannels' => true,
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields): void {
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Eyebrow', 'Eyebrow'),
                HTMLEditorField::create('Content', 'Text')->setRows(6),
                TextareaField::create('Note', 'Small note under widget')->setRows(2)
                    ->setDescription('e.g. "Saturday to Saturday in July & August. Min. 7 nights."'),
                CheckboxField::create('ShowChannels', 'Show Booking.com / Airbnb buttons'),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getType(): string
    {
        return 'Booking';
    }

    public function IsFlush(): bool
    {
        return false;
    }
}

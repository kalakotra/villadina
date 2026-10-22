<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Booking\Page\BookingPage;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Villa-wide settings: contact, channels, booking defaults, legal pages.
 *
 * @property string|null $VillaSubtitle
 * @property string|null $Email
 * @property string|null $Phone
 * @property string|null $WhatsApp
 * @property string|null $Address
 * @property string|null $PriceFrom
 * @property int $MaxGuests
 * @property string|null $BookingComURL
 * @property string|null $AirbnbURL
 * @property string|null $InstagramURL
 * @property string|null $FacebookURL
 * @method File Logo()
 * @method SiteTree InquiryPage()
 * @method SiteTree ImprintPage()
 * @method SiteTree PrivacyPage()
 * @extends Extension<SiteConfig>
 */
class SiteConfigExtension extends Extension
{
    private static array $db = [
        'VillaSubtitle' => 'Varchar(120)',
        'Email' => 'Varchar(254)',
        'Phone' => 'Varchar(50)',
        'WhatsApp' => 'Varchar(50)',
        'Address' => 'Text',
        'PriceFrom' => 'Varchar(80)',
        'MaxGuests' => 'Int',
        'BookingComURL' => 'Varchar(500)',
        'AirbnbURL' => 'Varchar(500)',
        'InstagramURL' => 'Varchar(500)',
        'FacebookURL' => 'Varchar(500)',
    ];

    private static array $has_one = [
        'Logo' => File::class,
        'InquiryPage' => SiteTree::class,
        'ImprintPage' => SiteTree::class,
        'PrivacyPage' => SiteTree::class,
    ];

    private static array $owns = [
        'Logo',
    ];

    private static array $defaults = [
        'VillaSubtitle' => 'Šumber · Labin · Istra',
        'MaxGuests' => 8,
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $tab = 'Root.Villa';

        $fields->addFieldsToTab($tab, [
            HeaderField::create('VillaBrandHeader', 'Brand', 3),
            TextField::create('VillaSubtitle', 'Subtitle (under logo)'),
            UploadField::create('Logo', 'Logo (SVG/PNG, dark version)')
                ->setFolderName('Branding')
                ->setAllowedExtensions(['svg', 'png', 'webp']),

            HeaderField::create('VillaContactHeader', 'Contact', 3),
            EmailField::create('Email', 'E-mail'),
            TextField::create('Phone', 'Phone')->setDescription('International format, e.g. +385 91 123 4567'),
            TextField::create('WhatsApp', 'WhatsApp number')->setDescription('Digits only with country code, e.g. 385911234567'),
            TextareaField::create('Address', 'Address')->setRows(3),

            HeaderField::create('VillaBookingHeader', 'Booking', 3),
            TextField::create('PriceFrom', 'Price label')->setDescription('e.g. "ab 180 € / Nacht" (translated per locale)'),
            NumericField::create('MaxGuests', 'Maximum guests'),
            TreeDropdownField::create('InquiryPageID', 'Booking page (target of all "Check availability" buttons)', SiteTree::class),
            TextField::create('BookingComURL', 'Booking.com URL'),
            TextField::create('AirbnbURL', 'Airbnb URL'),

            HeaderField::create('VillaSocialHeader', 'Social', 3),
            TextField::create('InstagramURL', 'Instagram URL'),
            TextField::create('FacebookURL', 'Facebook URL'),

            HeaderField::create('VillaLegalHeader', 'Legal', 3),
            TreeDropdownField::create('ImprintPageID', 'Impressum / Imprint', SiteTree::class),
            TreeDropdownField::create('PrivacyPageID', 'Datenschutz / Privacy', SiteTree::class),
        ]);
    }

    public function PhoneHref(): string
    {
        return 'tel:' . preg_replace('/[^\d+]/', '', (string) $this->owner->Phone);
    }

    public function WhatsAppHref(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->owner->WhatsApp);

        return $digits !== '' ? 'https://wa.me/' . $digits : '';
    }

    public function InquiryLink(): string
    {
        $page = $this->owner->InquiryPage();
        if (!$page->exists()) {
            $page = BookingPage::get()->first();
        }

        return $page && $page->exists() ? $page->Link() : '';
    }
}

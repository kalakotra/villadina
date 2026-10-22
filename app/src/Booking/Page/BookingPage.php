<?php

declare(strict_types=1);

namespace App\Booking\Page;

use Page;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TreeDropdownField;

/**
 * Booking request + payment pages.
 *
 *   /buchen                      request form with live price
 *   /buchen/received             thank-you after request
 *   /buchen/pay/{token}          payment choice (Stripe / bank transfer)
 *   /buchen/proforma/{token}     proforma PDF
 *   /buchen/success/{token}      return from Stripe
 *
 * @method SiteTree TermsPage()
 */
class BookingPage extends Page
{
    private static string $table_name = 'VD_BookingPage';
    private static string $singular_name = 'Booking page';
    private static string $plural_name = 'Booking pages';
    private static string $description = 'Booking request, live price and payment';
    private static string $icon_class = 'font-icon-calendar';
    private static bool $can_be_root = true;
    private static array $allowed_children = [];

    private static array $has_one = [
        'TermsPage' => SiteTree::class,
    ];

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->addFieldToTab(
            'Root.Main',
            TreeDropdownField::create('TermsPageID', 'Rental terms page (AGB / Uvjeti)', SiteTree::class),
            'Content',
        );

        return $fields;
    }

    public function canCreate($member = null, $context = []): bool
    {
        // One booking page per site (per locale via Fluent)
        return parent::canCreate($member, $context) && !self::get()->exists();
    }
}

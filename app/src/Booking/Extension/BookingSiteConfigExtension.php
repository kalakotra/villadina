<?php

declare(strict_types=1);

namespace App\Booking\Extension;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Booking rules, payment terms, tourist tax, bank details.
 *
 * @property float $DepositPercent
 * @property int $BalanceDaysBefore
 * @property int $DepositDays
 * @property int $BalanceReminderDays
 * @property int $MinLeadDays
 * @property string $TouristTaxAdult
 * @property int $TouristTaxChildPercent
 * @property bool $TouristTaxOnline
 * @property string|null $BookingNotifyEmail
 * @property string|null $OwnerName
 * @property string|null $OwnerAddress
 * @property string|null $OwnerTaxID
 * @property string|null $BankAccountHolder
 * @property string|null $BankName
 * @property string|null $IBAN
 * @property string|null $BIC
 * @property string|null $CancellationPolicy
 * @extends Extension<SiteConfig>
 */
class BookingSiteConfigExtension extends Extension
{
    private static array $db = [
        'DepositPercent' => 'Decimal(5,2)',
        'BalanceDaysBefore' => 'Int',
        'DepositDays' => 'Int',
        'BalanceReminderDays' => 'Int',
        'MinLeadDays' => 'Int',
        'TouristTaxAdult' => 'Decimal(6,2)',
        'TouristTaxChildPercent' => 'Int',
        'TouristTaxOnline' => 'Boolean',
        'BookingNotifyEmail' => 'Varchar(254)',
        'OwnerName' => 'Varchar(200)',
        'OwnerAddress' => 'Text',
        'OwnerTaxID' => 'Varchar(50)',
        'BankAccountHolder' => 'Varchar(200)',
        'BankName' => 'Varchar(200)',
        'IBAN' => 'Varchar(40)',
        'BIC' => 'Varchar(15)',
        'CancellationPolicy' => 'Text',
    ];

    private static array $defaults = [
        'DepositPercent' => 30,
        'BalanceDaysBefore' => 60,
        'DepositDays' => 3,
        'BalanceReminderDays' => 14,
        'MinLeadDays' => 2,
        'TouristTaxAdult' => 1.60,
        'TouristTaxChildPercent' => 50,
        'TouristTaxOnline' => true,
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $tab = 'Root.Booking';

        $fields->addFieldsToTab($tab, [
            HeaderField::create('BkTermsHeader', 'Payment terms', 3),
            NumericField::create('DepositPercent', 'Deposit (%)')->setScale(2),
            NumericField::create('BalanceDaysBefore', 'Balance due (days before arrival)')
                ->setDescription('Bookings made closer to arrival than this pay 100 % upfront.'),
            NumericField::create('DepositDays', 'Days to pay after approval'),
            NumericField::create('BalanceReminderDays', 'Send balance reminder (days before due date)'),
            NumericField::create('MinLeadDays', 'Minimum days between request and arrival'),
            TextareaField::create('CancellationPolicy', 'Cancellation policy (shown on booking page & proforma)')->setRows(4),

            HeaderField::create('BkTaxHeader', 'Tourist tax (boravišna pristojba)', 3),
            NumericField::create('TouristTaxAdult', 'Per adult / night (€)')->setScale(2)
                ->setDescription('Default; seasons can override.'),
            NumericField::create('TouristTaxChildPercent', 'Children 12–17 pay (%)')
                ->setDescription('Children under 12 are exempt.'),
            CheckboxField::create('TouristTaxOnline', 'Collect tourist tax with the booking (otherwise: payable on site)'),

            HeaderField::create('BkOwnerHeader', 'Landlord (proforma invoice)', 3),
            TextField::create('OwnerName', 'Name / company'),
            TextareaField::create('OwnerAddress', 'Address')->setRows(3),
            TextField::create('OwnerTaxID', 'OIB / VAT ID'),
            EmailField::create('BookingNotifyEmail', 'Notification e-mail for new requests & payments'),

            HeaderField::create('BkBankHeader', 'Bank transfer', 3),
            TextField::create('BankAccountHolder', 'Account holder'),
            TextField::create('BankName', 'Bank'),
            TextField::create('IBAN', 'IBAN'),
            TextField::create('BIC', 'BIC / SWIFT'),
            LiteralField::create('BkStripeNote', '<p class="form__field-description">Card / SEPA payments: set <code>STRIPE_SECRET_KEY</code> and <code>STRIPE_WEBHOOK_SECRET</code> in <code>.env</code>.</p>'),
        ]);
    }

    public function BankTransferEnabled(): bool
    {
        return trim((string) $this->owner->IBAN) !== '';
    }

    public function IBANFormatted(): string
    {
        return trim(chunk_split(strtoupper(preg_replace('/\s+/', '', (string) $this->owner->IBAN)), 4, ' '));
    }

    public function NotifyEmail(): string
    {
        return (string) ($this->owner->BookingNotifyEmail ?: $this->owner->Email);
    }
}

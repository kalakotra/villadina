<?php

declare(strict_types=1);

namespace App\Booking\Model;

use App\Booking\Money;
use App\Booking\Page\BookingPage;
use App\Booking\Service\PriceCalculator;
use DateTimeImmutable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\i18n\i18n;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * Status flow:
 *   requested → approved (dates held, waiting for deposit) → confirmed (deposit paid) → paid → completed
 *   requested → declined | approved → expired | any active → cancelled
 *
 * @property string $Reference
 * @property string $Status
 * @property string $Source
 * @property string $Arrival
 * @property string $Departure
 * @property int $Nights
 * @property int $Adults
 * @property int $Children  12–17 years (reduced tourist tax)
 * @property int $Infants   0–11 years (no tourist tax)
 * @property string $FirstName
 * @property string $LastName
 * @property string $Email
 * @property string|null $Phone
 * @property string|null $Street
 * @property string|null $PostalCode
 * @property string|null $City
 * @property string|null $Country
 * @property string|null $Message
 * @property string $Locale
 * @property int $AccommodationAmount
 * @property int $TouristTaxAmount
 * @property bool $TouristTaxIncluded
 * @property int $AdjustmentAmount
 * @property string|null $AdjustmentNote
 * @property int $TotalAmount
 * @property int $DepositAmount
 * @property int $BalanceAmount
 * @property string|null $PriceBreakdown JSON
 * @property string|null $DepositDueDate
 * @property string|null $BalanceDueDate
 * @property string $AccessToken
 * @property string|null $ApprovedAt
 * @property string|null $ConfirmedAt
 * @property string|null $CancelledAt
 * @property string|null $TermsAcceptedAt
 * @property string|null $BalanceReminderSentAt
 * @property string|null $OverdueNotifiedAt
 * @property string|null $DeclineReason
 * @property string|null $InternalNotes
 * @property bool $RecalculatePrice
 * @method HasManyList<Payment> Payments()
 */
class Reservation extends DataObject
{
    use BookingPermissions;

    /** Statuses that occupy the calendar. */
    public const BLOCKING = ['approved', 'confirmed', 'paid', 'completed'];

    /** Statuses in which the guest still owes money. */
    public const PAYABLE = ['approved', 'confirmed'];

    private static string $table_name = 'VD_Reservation';
    private static string $singular_name = 'Reservation';
    private static string $plural_name = 'Reservations';

    private static array $db = [
        'Reference' => 'Varchar(20)',
        'Status' => "Enum('requested,approved,confirmed,paid,completed,declined,expired,cancelled','requested')",
        'Source' => "Enum('website,manual','website')",
        'Arrival' => 'Date',
        'Departure' => 'Date',
        'Nights' => 'Int',
        'Adults' => 'Int',
        'Children' => 'Int',
        'Infants' => 'Int',
        'FirstName' => 'Varchar(100)',
        'LastName' => 'Varchar(100)',
        'Email' => 'Varchar(254)',
        'Phone' => 'Varchar(50)',
        'Street' => 'Varchar(200)',
        'PostalCode' => 'Varchar(20)',
        'City' => 'Varchar(100)',
        'Country' => 'Varchar(2)',
        'Message' => 'Text',
        'Locale' => 'Varchar(10)',
        'AccommodationAmount' => 'Int',
        'TouristTaxAmount' => 'Int',
        'TouristTaxIncluded' => 'Boolean',
        'AdjustmentAmount' => 'Int',
        'AdjustmentNote' => 'Varchar(255)',
        'TotalAmount' => 'Int',
        'DepositAmount' => 'Int',
        'BalanceAmount' => 'Int',
        'PriceBreakdown' => 'Text',
        'DepositDueDate' => 'Date',
        'BalanceDueDate' => 'Date',
        'AccessToken' => 'Varchar(64)',
        'ApprovedAt' => 'Datetime',
        'ConfirmedAt' => 'Datetime',
        'CancelledAt' => 'Datetime',
        'TermsAcceptedAt' => 'Datetime',
        'BalanceReminderSentAt' => 'Datetime',
        'OverdueNotifiedAt' => 'Datetime',
        'DeclineReason' => 'Text',
        'InternalNotes' => 'Text',
        'RecalculatePrice' => 'Boolean',
    ];

    private static array $has_many = [
        'Payments' => Payment::class,
    ];

    private static array $cascade_deletes = ['Payments'];

    private static array $indexes = [
        'Reference' => ['type' => 'unique', 'columns' => ['Reference']],
        'AccessToken' => ['type' => 'unique', 'columns' => ['AccessToken']],
        'Range' => ['type' => 'index', 'columns' => ['Arrival', 'Departure']],
        'Status' => true,
    ];

    private static array $defaults = [
        'Status' => 'requested',
        'Source' => 'manual',
        'Adults' => 2,
        'TouristTaxIncluded' => true,
        'RecalculatePrice' => true,
    ];

    private static string $default_sort = 'Arrival DESC';

    private static array $summary_fields = [
        'Reference' => 'Ref.',
        'Arrival.Nice' => 'Arrival',
        'Departure.Nice' => 'Departure',
        'GuestName' => 'Guest',
        'GuestCount' => 'Guests',
        'TotalFormatted' => 'Total',
        'PaidFormatted' => 'Paid',
        'StatusLabel' => 'Status',
    ];

    private static array $casting = [
        'DueNowDate' => 'Date',
    ];

    private static array $searchable_fields = [
        'Reference',
        'LastName',
        'Email',
        'Status',
    ];

    // ---------------------------------------------------------------------
    // CMS
    // ---------------------------------------------------------------------

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'Reference', 'AccessToken', 'Nights', 'Locale', 'PriceBreakdown',
            'AccommodationAmount', 'TouristTaxAmount', 'TouristTaxIncluded', 'AdjustmentAmount',
            'TotalAmount', 'DepositAmount', 'BalanceAmount', 'DepositDueDate', 'BalanceDueDate',
            'ApprovedAt', 'ConfirmedAt', 'CancelledAt', 'TermsAcceptedAt', 'BalanceReminderSentAt',
            'OverdueNotifiedAt', 'Payments', 'Status', 'Source', 'RecalculatePrice', 'DeclineReason',
            'InternalNotes', 'AdjustmentNote',
        ]);

        $main = [
            HeaderField::create('StayHeader', 'Stay', 3),
        ];
        if ($this->isInDB()) {
            array_unshift(
                $main,
                LiteralField::create('StatusBox', sprintf(
                    '<p class="alert alert-%s"><strong>%s</strong> · %s · %s</p>',
                    $this->statusTone(),
                    htmlspecialchars((string) $this->Reference),
                    htmlspecialchars($this->StatusLabel()),
                    htmlspecialchars($this->Source === 'website' ? 'Website request' : 'Manual'),
                )),
            );
        }
        $fields->addFieldsToTab('Root.Main', $main, 'Arrival');

        $fields->dataFieldByName('Departure')?->setTitle('Departure (checkout)');
        $fields->dataFieldByName('Children')?->setTitle('Children 12–17');
        $fields->dataFieldByName('Infants')?->setTitle('Children 0–11');
        $fields->addFieldToTab('Root.Main', HeaderField::create('GuestHeader', 'Guest', 3), 'FirstName');
        $fields->dataFieldByName('Message')?->setTitle('Guest message')->setRows(4);

        // Price
        $fields->addFieldsToTab('Root.Price', [
            LiteralField::create('PriceTable', $this->priceTableHTML()),
            NumericField::create('AdjustmentEuro', 'Adjustment (€, negative = discount)')->setScale(2),
            TextField::create('AdjustmentNote', 'Adjustment note (shown to guest)'),
            CheckboxField::create('RecalculatePrice', 'Recalculate price from seasons on save')
                ->setDescription('Only while the reservation is still a request.'),
        ]);

        if ($this->isInDB()) {
            $fields->addFieldToTab('Root.Payments', GridField::create(
                'Payments',
                'Payments',
                $this->Payments(),
                GridFieldConfig_RecordEditor::create(),
            ));
        }

        $fields->addFieldsToTab('Root.Internal', [
            TextareaField::create('InternalNotes', 'Internal notes'),
            TextareaField::create('DeclineReason', 'Reason for decline / cancellation (sent to guest)')->setRows(3),
            ReadonlyField::create('ApprovedAtRO', 'Approved', $this->ApprovedAt),
            ReadonlyField::create('ConfirmedAtRO', 'Confirmed', $this->ConfirmedAt),
            ReadonlyField::create('TermsAtRO', 'Terms accepted', $this->TermsAcceptedAt),
            ReadonlyField::create('LocaleRO', 'Language', $this->Locale),
        ]);

        if ($this->isInDB() && ($overlaps = $this->overlappingRequests())->exists()) {
            $fields->addFieldToTab('Root.Main', LiteralField::create('OverlapWarning', sprintf(
                '<p class="alert alert-warning">Overlapping requests: %s</p>',
                htmlspecialchars(implode(', ', $overlaps->column('Reference'))),
            )), 'StayHeader');
        }

        return $fields;
    }

    private function priceTableHTML(): string
    {
        $rows = '';
        foreach ($this->BreakdownLines() as $line) {
            $rows .= sprintf('<tr><td>%s</td><td class="text-end">%s</td></tr>', htmlspecialchars((string) $line->Label), htmlspecialchars((string) $line->Formatted));
        }

        $schedule = '';
        if ($this->DepositDueDate) {
            $schedule = sprintf(
                '<tr><td>Deposit due %s</td><td class="text-end">%s</td></tr>',
                htmlspecialchars((string) $this->DepositDueDate),
                Money::format((int) $this->DepositAmount),
            );
            if ((int) $this->BalanceAmount > 0) {
                $schedule .= sprintf(
                    '<tr><td>Balance due %s</td><td class="text-end">%s</td></tr>',
                    htmlspecialchars((string) $this->BalanceDueDate),
                    Money::format((int) $this->BalanceAmount),
                );
            }
        }

        return sprintf(
            '<table class="table table-sm" style="max-width:560px">%s<tr><th>Total</th><th class="text-end">%s</th></tr>%s<tr><td>Paid</td><td class="text-end">%s</td></tr><tr><td>Outstanding</td><td class="text-end">%s</td></tr></table>',
            $rows,
            $this->TotalFormatted(),
            $schedule,
            $this->PaidFormatted(),
            Money::format($this->OutstandingAmount()),
        );
    }

    public function getAdjustmentEuro(): string
    {
        return Money::toDecimal((int) $this->AdjustmentAmount);
    }

    public function setAdjustmentEuro(mixed $value): static
    {
        $this->AdjustmentAmount = Money::toCents((string) $value);

        return $this;
    }

    // ---------------------------------------------------------------------
    // Lifecycle
    // ---------------------------------------------------------------------

    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if (!$this->Arrival || !$this->Departure || $this->Departure <= $this->Arrival) {
            $result->addFieldError('Departure', 'Departure must be after arrival.');
        }
        if ((int) $this->Adults < 1) {
            $result->addFieldError('Adults', 'At least one adult.');
        }
        if (!$this->Email || !filter_var($this->Email, FILTER_VALIDATE_EMAIL)) {
            $result->addFieldError('Email', 'Valid e-mail required.');
        }

        if ($result->isValid() && $this->shouldRecalculate()) {
            $quote = Injector::inst()->get(PriceCalculator::class)->quote(
                new DateTimeImmutable($this->Arrival),
                new DateTimeImmutable($this->Departure),
                (int) $this->Adults,
                (int) $this->Children,
                (int) $this->Infants,
                $this->Source === 'manual', // owner may book outside rules (lead time etc.)
            );
            foreach ($quote->errors as $error) {
                $result->addError($error);
            }
        }

        return $result;
    }

    private function shouldRecalculate(): bool
    {
        return $this->Status === 'requested' && ($this->RecalculatePrice || !$this->isInDB());
    }

    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        if (!$this->AccessToken) {
            $this->AccessToken = bin2hex(random_bytes(24));
        }
        if (!$this->Locale) {
            $this->Locale = i18n::get_locale();
        }
        if ($this->Arrival && $this->Departure) {
            $this->Nights = (int) (new DateTimeImmutable($this->Arrival))->diff(new DateTimeImmutable($this->Departure))->days;
        }

        if ($this->shouldRecalculate()) {
            $quote = Injector::inst()->get(PriceCalculator::class)->quote(
                new DateTimeImmutable($this->Arrival),
                new DateTimeImmutable($this->Departure),
                (int) $this->Adults,
                (int) $this->Children,
                (int) $this->Infants,
                $this->Source === 'manual',
            );
            if ($quote->isValid()) {
                $this->applyQuoteAmounts($quote->accommodation, $quote->touristTax, $quote->touristTaxIncluded, $quote->linesForStorage());
            }
            $this->RecalculatePrice = false;
        } elseif ($this->isChanged('AdjustmentAmount') && $this->Status === 'requested') {
            $this->recomputeTotal();
        }
    }

    protected function onAfterWrite(): void
    {
        parent::onAfterWrite();

        if (!$this->Reference) {
            $year = $this->Arrival ? substr($this->Arrival, 0, 4) : date('Y');
            $this->Reference = sprintf('VD-%s-%04d', $year, $this->ID);
            $this->write();
        }
    }

    /**
     * @param list<array{label: string, amount: int}> $lines
     */
    public function applyQuoteAmounts(int $accommodation, int $tax, bool $taxIncluded, array $lines): void
    {
        $this->AccommodationAmount = $accommodation;
        $this->TouristTaxAmount = $tax;
        $this->TouristTaxIncluded = $taxIncluded;
        $this->PriceBreakdown = json_encode($lines, JSON_UNESCAPED_UNICODE);
        $this->recomputeTotal();
    }

    private function recomputeTotal(): void
    {
        $this->TotalAmount = max(0, (int) $this->AccommodationAmount
            + ($this->TouristTaxIncluded ? (int) $this->TouristTaxAmount : 0)
            + (int) $this->AdjustmentAmount);
    }

    // ---------------------------------------------------------------------
    // Domain helpers
    // ---------------------------------------------------------------------

    public function arrivalDate(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->Arrival);
    }

    public function departureDate(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->Departure);
    }

    public function isBlocking(): bool
    {
        return in_array($this->Status, self::BLOCKING, true);
    }

    public function PaidAmount(): int
    {
        return (int) $this->Payments()->filter('Status', 'paid')->sum('Amount');
    }

    public function OutstandingAmount(): int
    {
        return max(0, (int) $this->TotalAmount - $this->PaidAmount());
    }

    /**
     * What the guest has to pay now, or null.
     *
     * @return array{kind: string, amount: int, due: ?string}|null
     */
    public function dueNow(): ?array
    {
        $paid = $this->PaidAmount();

        if ($this->Status === 'approved') {
            $full = (int) $this->DepositAmount >= (int) $this->TotalAmount;
            $amount = ($full ? (int) $this->TotalAmount : (int) $this->DepositAmount) - $paid;

            return $amount > 0 ? ['kind' => $full ? 'full' : 'deposit', 'amount' => $amount, 'due' => $this->DepositDueDate] : null;
        }

        if ($this->Status === 'confirmed') {
            $amount = (int) $this->TotalAmount - $paid;

            return $amount > 0 ? ['kind' => 'balance', 'amount' => $amount, 'due' => $this->BalanceDueDate] : null;
        }

        return null;
    }

    public function DueNowAmount(): int
    {
        return $this->dueNow()['amount'] ?? 0;
    }

    public function DueNowFormatted(): string
    {
        return Money::format($this->DueNowAmount(), $this->Locale ?: null);
    }

    public function DueNowDate(): ?string
    {
        return $this->dueNow()['due'] ?? null;
    }

    public function DueNowKindLabel(): string
    {
        return match ($this->dueNow()['kind'] ?? null) {
            'deposit' => _t(self::class . '.KindDeposit', 'Deposit'),
            'balance' => _t(self::class . '.KindBalance', 'Balance'),
            'full' => _t(self::class . '.KindFull', 'Full amount'),
            default => '',
        };
    }

    public function IsFullPaymentUpfront(): bool
    {
        return (int) $this->DepositAmount >= (int) $this->TotalAmount;
    }

    public function overlappingRequests(): DataList
    {
        return self::get()
            ->exclude('ID', (int) $this->ID)
            ->filter([
                'Status' => array_merge(['requested'], self::BLOCKING),
                'Arrival:LessThan' => $this->Departure,
                'Departure:GreaterThan' => $this->Arrival,
            ]);
    }

    public function PaymentLink(): string
    {
        $page = BookingPage::get()->first();

        return $page ? $page->AbsoluteLink('pay/' . $this->AccessToken) : '';
    }

    public function ProformaLink(): string
    {
        $page = BookingPage::get()->first();

        return $page ? $page->AbsoluteLink('proforma/' . $this->AccessToken) : '';
    }

    // ---------------------------------------------------------------------
    // Template helpers
    // ---------------------------------------------------------------------

    public function getTitle(): string
    {
        return trim(($this->Reference ?: 'New') . ' – ' . $this->GuestName());
    }

    public function GuestName(): string
    {
        return trim($this->FirstName . ' ' . $this->LastName);
    }

    public function GuestCount(): int
    {
        return (int) $this->Adults + (int) $this->Children + (int) $this->Infants;
    }

    public function TotalFormatted(): string
    {
        return Money::format((int) $this->TotalAmount, $this->Locale ?: null);
    }

    public function PaidFormatted(): string
    {
        return Money::format($this->PaidAmount(), $this->Locale ?: null);
    }

    public function DepositFormatted(): string
    {
        return Money::format((int) $this->DepositAmount, $this->Locale ?: null);
    }

    public function BalanceFormatted(): string
    {
        return Money::format((int) $this->BalanceAmount, $this->Locale ?: null);
    }

    public function TouristTaxFormatted(): string
    {
        return Money::format((int) $this->TouristTaxAmount, $this->Locale ?: null);
    }

    public function BreakdownLines(): ArrayList
    {
        $lines = json_decode((string) $this->PriceBreakdown, true) ?: [];
        $out = ArrayList::create();
        foreach ($lines as $line) {
            $out->push(ArrayData::create([
                'Label' => $line['label'] ?? '',
                'Formatted' => Money::format((int) ($line['amount'] ?? 0), $this->Locale ?: null),
            ]));
        }
        if ((int) $this->AdjustmentAmount !== 0) {
            $out->push(ArrayData::create([
                'Label' => $this->AdjustmentNote ?: _t(self::class . '.Adjustment', 'Adjustment'),
                'Formatted' => Money::format((int) $this->AdjustmentAmount, $this->Locale ?: null),
            ]));
        }

        return $out;
    }

    public function StatusLabel(): string
    {
        return match ($this->Status) {
            'requested' => _t(self::class . '.StatusRequested', 'Request'),
            'approved' => _t(self::class . '.StatusApproved', 'Approved – awaiting deposit'),
            'confirmed' => _t(self::class . '.StatusConfirmed', 'Confirmed – deposit paid'),
            'paid' => _t(self::class . '.StatusPaid', 'Fully paid'),
            'completed' => _t(self::class . '.StatusCompleted', 'Completed'),
            'declined' => _t(self::class . '.StatusDeclined', 'Declined'),
            'expired' => _t(self::class . '.StatusExpired', 'Expired (unpaid)'),
            'cancelled' => _t(self::class . '.StatusCancelled', 'Cancelled'),
            default => (string) $this->Status,
        };
    }

    private function statusTone(): string
    {
        return match ($this->Status) {
            'requested' => 'info',
            'approved' => 'warning',
            'confirmed', 'paid', 'completed' => 'success',
            default => 'secondary',
        };
    }
}

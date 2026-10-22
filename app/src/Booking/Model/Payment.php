<?php

declare(strict_types=1);

namespace App\Booking\Model;

use App\Booking\Money;
use App\Booking\Service\ReservationService;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBDatetime;

/**
 * @property string $Kind deposit|balance|full
 * @property string $Method stripe|bank|cash
 * @property int $Amount cents
 * @property string $Status pending|paid|failed|cancelled|refunded
 * @property string|null $StripeSessionID
 * @property string|null $StripePaymentIntentID
 * @property string|null $PaidAt
 * @property string|null $Note
 * @method Reservation Reservation()
 */
class Payment extends DataObject
{
    use BookingPermissions;

    private static string $table_name = 'VD_Payment';
    private static string $singular_name = 'Payment';
    private static string $plural_name = 'Payments';

    private static array $db = [
        'Kind' => "Enum('deposit,balance,full','deposit')",
        'Method' => "Enum('stripe,bank,cash','bank')",
        'Amount' => 'Int',
        'Status' => "Enum('pending,paid,failed,cancelled,refunded','pending')",
        'StripeSessionID' => 'Varchar(255)',
        'StripePaymentIntentID' => 'Varchar(255)',
        'PaidAt' => 'Datetime',
        'Note' => 'Text',
    ];

    private static array $has_one = [
        'Reservation' => Reservation::class,
    ];

    private static array $indexes = [
        'StripeSessionID' => true,
    ];

    private static string $default_sort = 'Created DESC';

    private static array $summary_fields = [
        'Created.Nice' => 'Created',
        'KindLabel' => 'Type',
        'Method' => 'Method',
        'AmountFormatted' => 'Amount',
        'Status' => 'Status',
        'PaidAt.Nice' => 'Paid',
    ];

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['ReservationID', 'Amount']);
        $fields->addFieldToTab(
            'Root.Main',
            NumericField::create('AmountEuro', 'Amount (€)')->setScale(2),
            'Status',
        );
        $fields->replaceField('Kind', DropdownField::create('Kind', 'Type', [
            'deposit' => 'Deposit',
            'balance' => 'Balance',
            'full' => 'Full payment',
        ]));
        $fields->makeFieldReadonly(['StripeSessionID', 'StripePaymentIntentID']);

        return $fields;
    }

    public function getAmountEuro(): string
    {
        return Money::toDecimal((int) $this->Amount);
    }

    public function setAmountEuro(mixed $value): static
    {
        $this->Amount = Money::toCents((string) $value);

        return $this;
    }

    public function AmountFormatted(): string
    {
        return Money::format((int) $this->Amount);
    }

    public function KindLabel(): string
    {
        return match ($this->Kind) {
            'deposit' => _t(self::class . '.Deposit', 'Deposit'),
            'balance' => _t(self::class . '.Balance', 'Balance'),
            default => _t(self::class . '.Full', 'Full payment'),
        };
    }

    public function isPaid(): bool
    {
        return $this->Status === 'paid';
    }

    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        if ($this->isPaid() && !$this->PaidAt) {
            $this->PaidAt = DBDatetime::now()->Rfc2822();
        }
    }

    protected function onAfterWrite(): void
    {
        parent::onAfterWrite();

        // Manually recorded payments (CMS) update the reservation state too
        if ($this->isChanged('Status') && $this->isPaid() && $this->Reservation()->exists()) {
            Injector::inst()->get(ReservationService::class)->syncStatusFromPayments($this->Reservation());
        }
    }
}

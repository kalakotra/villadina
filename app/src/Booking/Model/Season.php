<?php

declare(strict_types=1);

namespace App\Booking\Model;

use App\Booking\Money;
use DateTimeImmutable;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;

/**
 * Price period. Nights from StartDate up to and including EndDate.
 *
 * weekly:  stays inside the season are whole weeks, arrival & departure on ArrivalDay,
 *          price = weeks × WeeklyPrice
 * nightly: price = nights × NightlyPrice
 *
 * @property string $Title
 * @property string $StartDate
 * @property string $EndDate
 * @property string $Mode
 * @property string $WeeklyPrice
 * @property string $NightlyPrice
 * @property int $MinNights
 * @property int $ArrivalDay ISO-8601 weekday, 0 = any
 * @property bool $OverrideTouristTax
 * @property string $TouristTaxAdult
 */
class Season extends DataObject implements PermissionProvider
{
    public const PERMISSION = 'VD_MANAGE_BOOKINGS';

    private static string $table_name = 'VD_Season';
    private static string $singular_name = 'Season';
    private static string $plural_name = 'Seasons';

    private static array $db = [
        'Title' => 'Varchar(80)',
        'StartDate' => 'Date',
        'EndDate' => 'Date',
        'Mode' => "Enum('weekly,nightly','nightly')",
        'WeeklyPrice' => 'Decimal(10,2)',
        'NightlyPrice' => 'Decimal(10,2)',
        'MinNights' => 'Int',
        'ArrivalDay' => 'Int',
        'OverrideTouristTax' => 'Boolean',
        'TouristTaxAdult' => 'Decimal(6,2)',
    ];

    private static array $indexes = [
        'Range' => ['type' => 'index', 'columns' => ['StartDate', 'EndDate']],
    ];

    private static array $defaults = [
        'Mode' => 'nightly',
        'MinNights' => 3,
        'ArrivalDay' => 0,
    ];

    private static string $default_sort = 'StartDate ASC';

    private static array $summary_fields = [
        'Title' => 'Season',
        'StartDate.Nice' => 'From',
        'EndDate.Nice' => 'To (last night)',
        'PriceSummary' => 'Price',
        'MinNights' => 'Min. nights',
        'ArrivalDayLabel' => 'Arrival',
    ];

    /**
     * @return array<int, string>
     */
    public static function weekdayOptions(): array
    {
        return [
            0 => _t(self::class . '.AnyDay', 'Any day'),
            1 => _t(self::class . '.Monday', 'Monday'),
            2 => _t(self::class . '.Tuesday', 'Tuesday'),
            3 => _t(self::class . '.Wednesday', 'Wednesday'),
            4 => _t(self::class . '.Thursday', 'Thursday'),
            5 => _t(self::class . '.Friday', 'Friday'),
            6 => _t(self::class . '.Saturday', 'Saturday'),
            7 => _t(self::class . '.Sunday', 'Sunday'),
        ];
    }

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->replaceField('Mode', DropdownField::create('Mode', 'Pricing', [
            'weekly' => 'Weekly (whole weeks, fixed changeover day)',
            'nightly' => 'Per night',
        ]));
        $fields->replaceField('ArrivalDay', DropdownField::create('ArrivalDay', 'Arrival / departure day', self::weekdayOptions())
            ->setDescription('Required for weekly seasons (usually Saturday).'));

        $fields->dataFieldByName('EndDate')?->setTitle('To (last night)')
            ->setDescription('Inclusive. A Sat–Sat season from 4 Jul to 29 Aug ends on Fri 28 Aug.');
        $fields->replaceField('WeeklyPrice', NumericField::create('WeeklyPrice', 'Price per week (€)')->setScale(2));
        $fields->replaceField('NightlyPrice', NumericField::create('NightlyPrice', 'Price per night (€)')->setScale(2));
        $fields->replaceField('OverrideTouristTax', CheckboxField::create('OverrideTouristTax', 'Different tourist tax in this season'));
        $fields->replaceField('TouristTaxAdult', NumericField::create('TouristTaxAdult', 'Tourist tax per adult / night (€)')->setScale(2));

        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if (!$this->StartDate || !$this->EndDate) {
            $result->addError('Start and end date are required.');

            return $result;
        }

        if ($this->EndDate < $this->StartDate) {
            $result->addFieldError('EndDate', 'End date must be on or after the start date.');
        }

        if ($this->Mode === 'weekly') {
            if ((float) $this->WeeklyPrice <= 0) {
                $result->addFieldError('WeeklyPrice', 'Weekly seasons need a weekly price.');
            }
            if ((int) $this->ArrivalDay === 0) {
                $result->addFieldError('ArrivalDay', 'Weekly seasons need a fixed arrival day.');
            } elseif ((int) (new DateTimeImmutable($this->StartDate))->format('N') !== (int) $this->ArrivalDay) {
                $result->addFieldError('StartDate', 'A weekly season must start on its arrival day.');
            }
        } elseif ((float) $this->NightlyPrice <= 0) {
            $result->addFieldError('NightlyPrice', 'Nightly seasons need a price per night.');
        }

        $overlap = self::get()
            ->exclude('ID', (int) $this->ID)
            ->filter([
                'StartDate:LessThanOrEqual' => $this->EndDate,
                'EndDate:GreaterThanOrEqual' => $this->StartDate,
            ])
            ->first();

        if ($overlap) {
            $result->addError(sprintf('Overlaps with season "%s" (%s – %s).', $overlap->Title, $overlap->StartDate, $overlap->EndDate));
        }

        return $result;
    }

    public function weeklyCents(): int
    {
        return Money::toCents($this->WeeklyPrice);
    }

    public function nightlyCents(): int
    {
        return Money::toCents($this->NightlyPrice);
    }

    public function isWeekly(): bool
    {
        return $this->Mode === 'weekly';
    }

    public function PriceSummary(): string
    {
        return $this->isWeekly()
            ? Money::format($this->weeklyCents()) . ' / ' . _t(self::class . '.Week', 'week')
            : Money::format($this->nightlyCents()) . ' / ' . _t(self::class . '.Night', 'night');
    }

    public function ArrivalDayLabel(): string
    {
        return self::weekdayOptions()[(int) $this->ArrivalDay] ?? '';
    }

    public function providePermissions(): array
    {
        return [
            self::PERMISSION => [
                'name' => 'Manage bookings, seasons and calendars',
                'category' => 'Villa Dina',
            ],
        ];
    }

    public function canView($member = null): bool
    {
        return true;
    }

    public function canEdit($member = null): bool
    {
        return Permission::check(self::PERMISSION, 'any', $member);
    }

    public function canDelete($member = null): bool
    {
        return Permission::check(self::PERMISSION, 'any', $member);
    }

    public function canCreate($member = null, $context = []): bool
    {
        return Permission::check(self::PERMISSION, 'any', $member);
    }
}

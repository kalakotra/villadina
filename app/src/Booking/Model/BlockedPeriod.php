<?php

declare(strict_types=1);

namespace App\Booking\Model;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\ORM\DataObject;

/**
 * Owner-managed closure (own use, maintenance). EndDate = checkout day (exclusive).
 *
 * @property string $Title
 * @property string $StartDate
 * @property string $EndDate
 */
class BlockedPeriod extends DataObject
{
    use BookingPermissions;

    private static string $table_name = 'VD_BlockedPeriod';
    private static string $singular_name = 'Blocked period';
    private static string $plural_name = 'Blocked periods';

    private static array $db = [
        'Title' => 'Varchar(120)',
        'StartDate' => 'Date',
        'EndDate' => 'Date',
    ];

    private static string $default_sort = 'StartDate DESC';

    private static array $summary_fields = [
        'Title' => 'Reason',
        'StartDate.Nice' => 'From',
        'EndDate.Nice' => 'To (checkout)',
    ];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->dataFieldByName('StartDate')?->setTitle('From (first night)');
        $fields->dataFieldByName('EndDate')?->setTitle('To (checkout day)');

        return $fields;
    }

    public function validate(): ValidationResult
    {
        $result = parent::validate();
        if (!$this->StartDate || !$this->EndDate || $this->EndDate <= $this->StartDate) {
            $result->addFieldError('EndDate', 'Checkout day must be after the first night.');
        }

        return $result;
    }
}

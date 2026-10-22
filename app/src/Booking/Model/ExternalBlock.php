<?php

declare(strict_types=1);

namespace App\Booking\Model;

use SilverStripe\ORM\DataObject;

/**
 * Busy period imported from an iCal feed. EndDate = checkout day (exclusive).
 *
 * @property string $UID
 * @property string $StartDate
 * @property string $EndDate
 * @property string|null $Summary
 * @method ICalFeed Feed()
 */
class ExternalBlock extends DataObject
{
    use BookingPermissions;

    private static string $table_name = 'VD_ExternalBlock';
    private static string $singular_name = 'Imported block';
    private static string $plural_name = 'Imported blocks';

    private static array $db = [
        'UID' => 'Varchar(255)',
        'StartDate' => 'Date',
        'EndDate' => 'Date',
        'Summary' => 'Varchar(255)',
    ];

    private static array $has_one = [
        'Feed' => ICalFeed::class,
    ];

    private static array $indexes = [
        'FeedUID' => ['type' => 'unique', 'columns' => ['FeedID', 'UID']],
        'Range' => ['type' => 'index', 'columns' => ['StartDate', 'EndDate']],
    ];

    private static string $default_sort = 'StartDate ASC';

    private static array $summary_fields = [
        'StartDate.Nice' => 'From',
        'EndDate.Nice' => 'To (checkout)',
        'Summary' => 'Summary',
    ];

    public function canEdit($member = null): bool
    {
        return false;
    }

    public function canCreate($member = null, $context = []): bool
    {
        return false;
    }
}

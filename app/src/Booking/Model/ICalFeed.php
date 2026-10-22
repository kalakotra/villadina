<?php

declare(strict_types=1);

namespace App\Booking\Model;

use SilverStripe\Control\Director;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordViewer;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * One channel (Booking.com, Airbnb …):
 *  - ImportURL: the channel's export link → busy dates imported here
 *  - ExportURL: our link → paste into the channel's "import calendar"
 *    (contains our reservations, manual blocks and blocks of all *other* feeds)
 *
 * @property string $Title
 * @property string $Source
 * @property string|null $ImportURL
 * @property string $ExportToken
 * @property bool $Active
 * @property string|null $LastSyncedAt
 * @property string|null $LastError
 * @method HasManyList<ExternalBlock> Blocks()
 */
class ICalFeed extends DataObject
{
    use BookingPermissions;

    private static string $table_name = 'VD_ICalFeed';
    private static string $singular_name = 'Calendar sync';
    private static string $plural_name = 'Calendar sync';

    private static array $db = [
        'Title' => 'Varchar(80)',
        'Source' => "Enum('booking,airbnb,other','other')",
        'ImportURL' => 'Varchar(1000)',
        'ExportToken' => 'Varchar(64)',
        'Active' => 'Boolean',
        'LastSyncedAt' => 'Datetime',
        'LastError' => 'Text',
    ];

    private static array $has_many = [
        'Blocks' => ExternalBlock::class,
    ];

    private static array $cascade_deletes = ['Blocks'];

    private static array $indexes = [
        'ExportToken' => ['type' => 'unique', 'columns' => ['ExportToken']],
    ];

    private static array $defaults = [
        'Active' => true,
    ];

    private static array $summary_fields = [
        'Title' => 'Channel',
        'Active.Nice' => 'Active',
        'LastSyncedAt.Nice' => 'Last sync',
        'Blocks.Count' => 'Imported blocks',
    ];

    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['ExportToken', 'Blocks', 'LastSyncedAt', 'LastError']);

        $fields->dataFieldByName('ImportURL')?->setTitle('Import URL (channel → us)')
            ->setDescription('Booking.com: Rates & availability → Sync calendars → Export. Airbnb: Calendar → Availability → Export calendar.');

        if ($this->isInDB()) {
            $fields->addFieldsToTab('Root.Main', [
                ReadonlyField::create('ExportURLDisplay', 'Export URL (us → channel)', $this->ExportURL())
                    ->setDescription('Paste this into the channel\'s "Import calendar".'),
                ReadonlyField::create('LastSyncedAtDisplay', 'Last sync', $this->LastSyncedAt),
                ReadonlyField::create('LastErrorDisplay', 'Last error', $this->LastError ?: '—'),
            ]);
            $fields->addFieldToTab('Root.Blocks', GridField::create(
                'Blocks',
                'Imported blocks',
                $this->Blocks(),
                GridFieldConfig_RecordViewer::create(),
            ));
        }

        return $fields;
    }

    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        if (!$this->ExportToken) {
            $this->ExportToken = bin2hex(random_bytes(24));
        }
    }

    public function ExportURL(): string
    {
        return Director::absoluteURL('booking/ical/' . $this->ExportToken . '.ics');
    }
}

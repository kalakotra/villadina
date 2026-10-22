<?php

declare(strict_types=1);

namespace App\Booking\Admin;

use App\Booking\Model\BlockedPeriod;
use App\Booking\Model\ICalFeed;
use App\Booking\Model\Reservation;
use App\Booking\Model\Season;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldExportButton;
use SilverStripe\Forms\GridField\GridFieldImportButton;
use SilverStripe\Forms\GridField\GridFieldPrintButton;
use SilverStripe\ORM\DataList;

class BookingAdmin extends ModelAdmin
{
    private static string $url_segment = 'bookings';
    private static string $menu_title = 'Bookings';
    private static string $menu_icon_class = 'font-icon-calendar';
    private static int $menu_priority = 5;
    private static string $required_permission_codes = Season::PERMISSION;

    private static array $managed_models = [
        Reservation::class => ['title' => 'Reservations'],
        Season::class => ['title' => 'Seasons & prices'],
        BlockedPeriod::class => ['title' => 'Blocked periods'],
        ICalFeed::class => ['title' => 'Calendar sync'],
    ];

    private static array $model_importers = [];

    public function getList(): DataList
    {
        $list = parent::getList();

        // Reservations: open ones first (by arrival), then history
        if ($this->modelClass === Reservation::class) {
            $list = $list->orderBy(
                "CASE WHEN \"VD_Reservation\".\"Status\" IN ('requested','approved','confirmed','paid') THEN 0 ELSE 1 END ASC, "
                . "\"VD_Reservation\".\"Arrival\" ASC"
            );
        }

        return $list;
    }

    protected function getGridFieldConfig(): GridFieldConfig
    {
        $config = parent::getGridFieldConfig();
        $config->removeComponentsByType([GridFieldImportButton::class, GridFieldPrintButton::class]);
        if ($this->modelClass !== Reservation::class) {
            $config->removeComponentsByType(GridFieldExportButton::class);
        }

        return $config;
    }
}

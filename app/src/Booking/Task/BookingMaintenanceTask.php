<?php

declare(strict_types=1);

namespace App\Booking\Task;

use App\Booking\Service\ReservationService;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Daily: release unpaid approvals, balance reminders, overdue alerts, complete past stays.
 *
 * cron: 15 6 * * *  cd /var/www/villadina && vendor/bin/sake tasks:booking-maintenance
 */
class BookingMaintenanceTask extends BuildTask
{
    protected static string $commandName = 'booking-maintenance';
    protected string $title = 'Booking maintenance';
    protected static string $description = 'Expire unpaid approvals, send balance reminders and overdue alerts, complete past stays.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $stats = ReservationService::singleton()->runMaintenance();

        foreach ($stats as $key => $count) {
            $output->writeln(sprintf('%-10s %d', $key, $count));
        }

        return Command::SUCCESS;
    }
}

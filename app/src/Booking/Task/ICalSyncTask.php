<?php

declare(strict_types=1);

namespace App\Booking\Task;

use App\Booking\ICal\ICalSync;
use App\Booking\Model\ICalFeed;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Throwable;

/**
 * Pull busy dates from Booking.com / Airbnb.
 *
 * cron: 7,37 * * * *  cd /var/www/villadina && vendor/bin/sake tasks:booking-ical-sync
 */
class ICalSyncTask extends BuildTask
{
    protected static string $commandName = 'booking-ical-sync';
    protected string $title = 'Booking iCal sync';
    protected static string $description = 'Import busy dates from all active channel calendars.';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $sync = Injector::inst()->get(ICalSync::class);
        $failed = 0;

        foreach (ICalFeed::get()->filter('Active', true) as $feed) {
            try {
                $result = $sync->import($feed);
                $output->writeln(sprintf('%s: %d updated, %d removed', $feed->Title, $result['imported'], $result['removed']));
            } catch (Throwable $e) {
                $failed++;
                $output->writeln(sprintf('<error>%s: %s</error>', $feed->Title, $e->getMessage()));
            }
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace App\Task;

use App\Elements\Items\Distance;
use App\Elements\Items\Place;
use App\Geo\RouteFromVillaExtension;
use App\Geo\RouteService;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

/**
 * Recalculates driving distance/time from the villa for all places and distance rows.
 *
 *   vendor/bin/sake tasks:villa-distances          # only missing
 *   vendor/bin/sake tasks:villa-distances --all    # everything
 *
 * Villa coordinates: Settings → Villa → Latitude/Longitude.
 */
class PlaceDistancesTask extends BuildTask
{
    protected static string $commandName = 'villa-distances';
    protected string $title = 'Villa Dina: driving distances from the villa';
    protected static string $description = 'Calculates km and minutes by car (OSRM) from the villa to every place with coordinates.';

    /** @var list<class-string<DataObject>> */
    private static array $classes = [Place::class, Distance::class];

    public function getOptions(): array
    {
        return [
            new InputOption('all', null, InputOption::VALUE_NONE, 'Recalculate also places that already have a route'),
        ];
    }

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (!RouteService::origin()) {
            $output->writeln('<error>Villa coordinates missing: Settings → Villa → Latitude/Longitude.</error>');

            return Command::FAILURE;
        }

        $run = static fn () => self::refresh($output, (bool) $input->getOption('all'));

        // Write in the default locale (non-translated fields live in the base table)
        if (class_exists(FluentState::class) && ($default = Locale::getDefault())) {
            FluentState::singleton()->withState(function (FluentState $state) use ($default, $run): void {
                $state->setLocale($default->Locale);
                $state->setIsFrontend(false);
                $run();
            });
        } else {
            $run();
        }

        return Command::SUCCESS;
    }

    /**
     * Batch calculation with one OSRM table request; straight-line distance as fallback.
     */
    public static function refresh(PolyOutput $output, bool $all = true): void
    {
        $origin = RouteService::origin();
        if (!$origin) {
            $output->writeln('<comment>Distances skipped: villa coordinates not set.</comment>');

            return;
        }

        /** @var array<string, DataObject> $records */
        $records = [];
        $points = [];
        foreach ((array) static::config()->get('classes') as $class) {
            if (!class_exists($class)) {
                continue;
            }
            foreach (DataObject::get($class) as $record) {
                if (!$record->hasMethod('HasGeo') || !$record->HasGeo()) {
                    continue;
                }
                if (!$all && (float) $record->DriveKm > 0) {
                    continue;
                }
                $key = $class . '#' . $record->ID;
                $records[$key] = $record;
                $points[$key] = ['lat' => (float) $record->Latitude, 'lng' => (float) $record->Longitude];
            }
        }

        if (!$records) {
            $output->writeln('Distances: nothing to calculate.');

            return;
        }

        $service = RouteService::singleton();
        $routes = $service->table($origin, $points);

        $previous = RouteFromVillaExtension::$autoRoute;
        RouteFromVillaExtension::$autoRoute = false;
        $ok = 0;

        try {
            foreach ($records as $key => $record) {
                $found = $record->calculateRoute($service, $routes[$key] ?? null, false);
                $record->write();
                if ($found) {
                    $ok++;
                }
                $output->writeln(sprintf('  %s: %s', $record->Title, $record->DriveLabel() ?: '–'));
            }
        } finally {
            RouteFromVillaExtension::$autoRoute = $previous;
        }

        $output->writeln(sprintf('Distances: %d of %d by road%s', $ok, count($records), $ok < count($records)
            ? ' (rest straight-line – OSRM not reachable? Run tasks:villa-distances --all later)'
            : ''));
    }
}

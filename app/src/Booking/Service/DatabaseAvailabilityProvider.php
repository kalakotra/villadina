<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\AvailabilityProviderInterface;
use App\Booking\Model\BlockedPeriod;
use App\Booking\Model\ExternalBlock;
use App\Booking\Model\Reservation;
use DateTimeImmutable;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Busy = approved/confirmed/paid reservations + owner blocks + iCal imports.
 * All ranges are [start, checkout) — the checkout day itself stays bookable as arrival.
 */
class DatabaseAvailabilityProvider implements AvailabilityProviderInterface
{
    public function getBlockedNights(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $nights = [];
        foreach ($this->busyRanges($from, $to) as [$start, $end]) {
            $d = max(new DateTimeImmutable($start), $from);
            $stop = min(new DateTimeImmutable($end), $to);
            for (; $d < $stop; $d = $d->modify('+1 day')) {
                $nights[$d->format('Y-m-d')] = true;
            }
        }

        $list = array_keys($nights);
        sort($list);

        return $list;
    }

    public function isAvailable(DateTimeImmutable $arrival, DateTimeImmutable $departure, ?int $excludeReservationID = null): bool
    {
        return $this->busyRanges($arrival, $departure, $excludeReservationID) === [];
    }

    public function getMaxGuests(): int
    {
        return max(1, (int) SiteConfig::current_site_config()->MaxGuests ?: 8);
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    private function busyRanges(DateTimeImmutable $from, DateTimeImmutable $to, ?int $excludeReservationID = null): array
    {
        $f = $from->format('Y-m-d');
        $t = $to->format('Y-m-d');
        $ranges = [];

        $reservations = Reservation::get()->filter([
            'Status' => Reservation::BLOCKING,
            'Arrival:LessThan' => $t,
            'Departure:GreaterThan' => $f,
        ]);
        if ($excludeReservationID) {
            $reservations = $reservations->exclude('ID', $excludeReservationID);
        }
        foreach ($reservations as $r) {
            $ranges[] = [$r->Arrival, $r->Departure];
        }

        $overlap = ['StartDate:LessThan' => $t, 'EndDate:GreaterThan' => $f];
        foreach (BlockedPeriod::get()->filter($overlap) as $b) {
            $ranges[] = [$b->StartDate, $b->EndDate];
        }
        foreach (ExternalBlock::get()->filter($overlap)->filter('Feed.Active', true) as $b) {
            $ranges[] = [$b->StartDate, $b->EndDate];
        }

        return $ranges;
    }
}

<?php

declare(strict_types=1);

namespace App\Booking;

use DateTimeImmutable;

interface AvailabilityProviderInterface
{
    /**
     * Occupied nights (Y-m-d of the night's date) within [from, to).
     *
     * @return list<string>
     */
    public function getBlockedNights(DateTimeImmutable $from, DateTimeImmutable $to): array;

    /**
     * True when every night in [arrival, departure) is free.
     */
    public function isAvailable(DateTimeImmutable $arrival, DateTimeImmutable $departure, ?int $excludeReservationID = null): bool;

    public function getMaxGuests(): int;
}

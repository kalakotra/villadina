<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\Model\Season;
use App\Booking\Money;
use DateTimeImmutable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Seasonal pricing:
 *  - weekly seasons: whole weeks, changeover on the season's arrival day, weeks × weekly price
 *  - nightly seasons: nights × nightly price
 *  - stays may span seasons; each segment follows its own season's rules
 *  - tourist tax per adult/night, children 12–17 reduced, under 12 free
 */
class PriceCalculator
{
    use Injectable;

    public const MAX_NIGHTS = 60;

    public function __construct(private readonly PaymentSchedule $schedule = new PaymentSchedule())
    {
    }

    /**
     * @param bool $ownerOverride CMS bookings: skip lead time, min-nights and changeover rules
     */
    public function quote(
        ?DateTimeImmutable $arrival,
        ?DateTimeImmutable $departure,
        int $adults,
        int $children = 0,
        int $infants = 0,
        bool $ownerOverride = false,
        ?DateTimeImmutable $today = null,
    ): Quote {
        $config = SiteConfig::current_site_config();
        $today ??= new DateTimeImmutable('today');

        if (!$arrival || !$departure) {
            return Quote::invalid([_t('VDBooking.ErrSelectDates', 'Please select arrival and departure.')]);
        }

        $arrival = $arrival->setTime(0, 0);
        $departure = $departure->setTime(0, 0);

        if ($departure <= $arrival) {
            return Quote::invalid([_t('VDBooking.ErrDepartureAfterArrival', 'Departure must be after arrival.')], $arrival, $departure);
        }

        $nights = (int) $arrival->diff($departure)->days;
        $errors = [];

        if ($nights > self::MAX_NIGHTS) {
            $errors[] = _t('VDBooking.ErrMaxNights', 'For stays longer than {max} nights please contact us.', ['max' => self::MAX_NIGHTS]);
        }

        if (!$ownerOverride) {
            $lead = max(0, (int) $config->MinLeadDays);
            if ($arrival < $today->modify("+{$lead} days")) {
                $errors[] = _t('VDBooking.ErrLeadTime', 'Online requests are possible from {days} days before arrival. Please contact us directly.', ['days' => $lead]);
            }
        }

        $guests = $adults + $children + $infants;
        $maxGuests = max(1, (int) $config->MaxGuests ?: 8);
        if ($adults < 1) {
            $errors[] = _t('VDBooking.ErrAdults', 'At least one adult is required.');
        }
        if ($guests > $maxGuests) {
            $errors[] = _t('VDBooking.ErrMaxGuests', 'The villa sleeps up to {max} guests.', ['max' => $maxGuests]);
        }

        if ($errors !== []) {
            return Quote::invalid($errors, $arrival, $departure);
        }

        // --- Map each night to a season ----------------------------------
        $lastNight = $departure->modify('-1 day');
        $seasons = Season::get()
            ->filter([
                'StartDate:LessThanOrEqual' => $lastNight->format('Y-m-d'),
                'EndDate:GreaterThanOrEqual' => $arrival->format('Y-m-d'),
            ])
            ->sort('StartDate')
            ->toArray();

        /** @var list<array{season: Season, start: DateTimeImmutable, nights: int}> $segments */
        $segments = [];
        for ($d = $arrival; $d < $departure; $d = $d->modify('+1 day')) {
            $key = $d->format('Y-m-d');
            $season = null;
            foreach ($seasons as $s) {
                if ($s->StartDate <= $key && $s->EndDate >= $key) {
                    $season = $s;
                    break;
                }
            }

            if (!$season) {
                return Quote::invalid([
                    _t('VDBooking.ErrNoPrices', 'Prices for {date} are not published yet. Please contact us.', ['date' => $d->format('d.m.Y')]),
                ], $arrival, $departure);
            }

            $last = array_key_last($segments);
            if ($last !== null && $segments[$last]['season']->ID === $season->ID) {
                $segments[$last]['nights']++;
            } else {
                $segments[] = ['season' => $season, 'start' => $d, 'nights' => 1];
            }
        }

        // --- Rules ---------------------------------------------------------
        if (!$ownerOverride) {
            $minNights = max(1, (int) $segments[0]['season']->MinNights);
            if ($nights < $minNights) {
                $errors[] = _t('VDBooking.ErrMinNights', 'Minimum stay in this period: {count} nights.', ['count' => $minNights]);
            }

            foreach ($segments as $seg) {
                /** @var Season $season */
                $season = $seg['season'];
                if (!$season->isWeekly()) {
                    continue;
                }
                $startsOnDay = (int) $seg['start']->format('N') === (int) $season->ArrivalDay;
                if (!$startsOnDay || $seg['nights'] % 7 !== 0) {
                    $errors[] = _t(
                        'VDBooking.ErrWeekly',
                        '{season}: weekly stays only, {day} to {day}.',
                        ['season' => $season->Title, 'day' => $season->ArrivalDayLabel()],
                    );
                }
            }

            if ($errors !== []) {
                return Quote::invalid(array_values(array_unique($errors)), $arrival, $departure);
            }
        }

        // --- Accommodation ------------------------------------------------
        $lines = [];
        $accommodation = 0;
        foreach ($segments as $seg) {
            /** @var Season $season */
            $season = $seg['season'];
            $n = $seg['nights'];

            if ($season->isWeekly()) {
                $weekly = $season->weeklyCents();
                $weeks = intdiv($n, 7);
                $rest = $n % 7;
                $perNight = (int) round($weekly / 7);
                $parts = [];
                if ($weeks > 0) {
                    $parts[] = _t('VDBooking.LineWeeks', '{season}: {count} × week at {price}', ['season' => $season->Title, 'count' => $weeks, 'price' => Money::format($weekly)]);
                }
                if ($rest > 0) {
                    $parts[] = $weeks > 0
                        ? _t('VDBooking.LineNightsShort', '{count} nights', ['count' => $rest])
                        : _t('VDBooking.LineNights', '{season}: {count} nights at {price}', ['season' => $season->Title, 'count' => $rest, 'price' => Money::format($perNight)]);
                }
                $amount = $weeks * $weekly + $rest * $perNight;
                $label = implode(' + ', $parts);
            } else {
                $nightly = $season->nightlyCents();
                $amount = $n * $nightly;
                $label = _t('VDBooking.LineNights', '{season}: {count} nights at {price}', ['season' => $season->Title, 'count' => $n, 'price' => Money::format($nightly)]);
            }

            $lines[] = ['label' => $label, 'amount' => $amount];
            $accommodation += $amount;
        }

        // --- Tourist tax --------------------------------------------------
        $defaultRate = Money::toCents($config->TouristTaxAdult);
        $childPercent = max(0, min(100, (int) $config->TouristTaxChildPercent));
        $tax = 0;
        foreach ($segments as $seg) {
            $season = $seg['season'];
            $rate = $season->OverrideTouristTax ? Money::toCents($season->TouristTaxAdult) : $defaultRate;
            $perNight = $rate * $adults + (int) round($rate * $children * $childPercent / 100);
            $tax += $perNight * $seg['nights'];
        }

        $taxOnline = (bool) $config->TouristTaxOnline;
        $taxLabel = $taxOnline
            ? _t('VDBooking.LineTouristTax', 'Tourist tax')
            : _t('VDBooking.LineTouristTaxOnSite', 'Tourist tax (payable on site)');

        $total = $accommodation + ($taxOnline ? $tax : 0);
        $plan = $this->schedule->compute($total, $arrival, $today);

        return new Quote(
            arrival: $arrival,
            departure: $departure,
            nights: $nights,
            adults: $adults,
            children: $children,
            infants: $infants,
            lines: $lines,
            accommodation: $accommodation,
            touristTax: $tax,
            touristTaxIncluded: $taxOnline,
            touristTaxLabel: $taxLabel,
            total: $total,
            deposit: $plan['deposit'],
            balance: $plan['balance'],
            balanceDue: $plan['balanceDue'],
            errors: [],
        );
    }
}

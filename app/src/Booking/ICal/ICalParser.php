<?php

declare(strict_types=1);

namespace App\Booking\ICal;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Minimal RFC 5545 VEVENT reader — enough for Booking.com / Airbnb / VRBO exports
 * (all-day DTSTART/DTEND, occasionally date-times).
 */
final class ICalParser
{
    /**
     * @return list<array{uid: string, start: string, end: string, summary: string}>
     */
    public function parse(string $ics): array
    {
        // Unfold continuation lines (CRLF/LF followed by space or tab)
        $ics = (string) preg_replace("/\r?\n[ \t]/", '', $ics);
        $lines = preg_split("/\r?\n/", $ics) ?: [];

        $events = [];
        $current = null;

        foreach ($lines as $line) {
            if ($line === 'BEGIN:VEVENT') {
                $current = [];
                continue;
            }
            if ($line === 'END:VEVENT') {
                if ($current !== null && ($event = $this->toEvent($current))) {
                    $events[] = $event;
                }
                $current = null;
                continue;
            }
            if ($current === null || !str_contains($line, ':')) {
                continue;
            }

            [$nameWithParams, $value] = explode(':', $line, 2);
            $parts = explode(';', $nameWithParams);
            $name = strtoupper(array_shift($parts));
            $params = [];
            foreach ($parts as $p) {
                if (str_contains($p, '=')) {
                    [$k, $v] = explode('=', $p, 2);
                    $params[strtoupper($k)] = trim($v, '"');
                }
            }
            $current[$name] = ['value' => $value, 'params' => $params];
        }

        return $events;
    }

    /**
     * @param array<string, array{value: string, params: array<string, string>}> $props
     * @return array{uid: string, start: string, end: string, summary: string}|null
     */
    private function toEvent(array $props): ?array
    {
        if (!isset($props['DTSTART'])) {
            return null;
        }
        if (isset($props['STATUS']) && strtoupper($props['STATUS']['value']) === 'CANCELLED') {
            return null;
        }

        $start = $this->toDate($props['DTSTART']);
        $end = isset($props['DTEND']) ? $this->toDate($props['DTEND']) : null;
        if (!$start) {
            return null;
        }
        if (!$end || $end <= $start) {
            $end = $start->modify('+1 day');
        }

        $summary = $this->unescape($props['SUMMARY']['value'] ?? '');
        $uid = trim($props['UID']['value'] ?? '') ?: sha1($start->format('Ymd') . $end->format('Ymd') . $summary);

        return [
            'uid' => mb_substr($uid, 0, 255),
            'start' => $start->format('Y-m-d'),
            'end' => $end->format('Y-m-d'),
            'summary' => mb_substr($summary, 0, 255),
        ];
    }

    /**
     * @param array{value: string, params: array<string, string>} $prop
     */
    private function toDate(array $prop): ?DateTimeImmutable
    {
        $v = trim($prop['value']);

        if (preg_match('/^(\d{8})$/', $v, $m)) {
            return DateTimeImmutable::createFromFormat('!Ymd', $m[1]) ?: null;
        }

        if (preg_match('/^(\d{8}T\d{6})(Z?)$/', $v, $m)) {
            $tz = $m[2] === 'Z'
                ? new DateTimeZone('UTC')
                : $this->timezone($prop['params']['TZID'] ?? null);
            $dt = DateTimeImmutable::createFromFormat('Ymd\THis', $m[1], $tz);
            if (!$dt) {
                return null;
            }
            // Convert to the villa's local date, then truncate to the day
            $local = $dt->setTimezone(new DateTimeZone('Europe/Zagreb'));

            return $local->setTime(0, 0);
        }

        return null;
    }

    private function timezone(?string $tzid): DateTimeZone
    {
        try {
            return new DateTimeZone($tzid ?: 'Europe/Zagreb');
        } catch (\Exception) {
            return new DateTimeZone('Europe/Zagreb');
        }
    }

    private function unescape(string $v): string
    {
        return str_replace(['\\n', '\\N', '\\,', '\\;', '\\\\'], ["\n", "\n", ',', ';', '\\'], $v);
    }
}

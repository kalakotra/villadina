<?php

declare(strict_types=1);

namespace App\Booking\ICal;

use App\Booking\Model\BlockedPeriod;
use App\Booking\Model\ExternalBlock;
use App\Booking\Model\ICalFeed;
use App\Booking\Model\Reservation;
use DateTimeImmutable;
use GuzzleHttp\Client;
use SilverStripe\Control\Director;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;
use Throwable;

/**
 * Import: channel feeds → ExternalBlock
 * Export: our busy dates → per-channel .ics (excluding that channel's own blocks → no echo loops)
 */
class ICalSync
{
    public function __construct(
        private readonly ICalParser $parser = new ICalParser(),
        private ?Client $http = null,
    ) {
    }

    /**
     * @return array{imported: int, removed: int}
     */
    public function import(ICalFeed $feed): array
    {
        if (!$feed->ImportURL) {
            return ['imported' => 0, 'removed' => 0];
        }

        try {
            $response = $this->http()->get($feed->ImportURL, [
                'timeout' => 20,
                'headers' => ['User-Agent' => 'VillaDina-Calendar/1.0', 'Accept' => 'text/calendar'],
            ]);
            $body = (string) $response->getBody();
            if (!str_contains($body, 'BEGIN:VCALENDAR')) {
                throw new \UnexpectedValueException('Response is not an iCalendar file.');
            }

            $events = $this->parser->parse($body);
            $yesterday = (new DateTimeImmutable('yesterday'))->format('Y-m-d');
            $seen = [];
            $imported = 0;

            foreach ($events as $e) {
                if ($e['end'] < $yesterday) {
                    continue; // past – ignore
                }
                $seen[] = $e['uid'];

                $block = ExternalBlock::get()->filter(['FeedID' => $feed->ID, 'UID' => $e['uid']])->first()
                    ?? ExternalBlock::create(['FeedID' => $feed->ID, 'UID' => $e['uid']]);
                $block->StartDate = $e['start'];
                $block->EndDate = $e['end'];
                $block->Summary = $e['summary'];
                if ($block->isChanged()) {
                    $block->write();
                    $imported++;
                }
            }

            // Remove blocks that disappeared from the feed (cancellations) or lie in the past
            $stale = $feed->Blocks();
            if ($seen !== []) {
                $stale = $stale->exclude('UID', $seen);
            }
            $removed = 0;
            foreach ($stale as $block) {
                $block->delete();
                $removed++;
            }

            $feed->LastSyncedAt = DBDatetime::now()->Rfc2822();
            $feed->LastError = null;
            $feed->write();

            return ['imported' => $imported, 'removed' => $removed];
        } catch (Throwable $e) {
            $feed->LastError = mb_substr(DBDatetime::now()->Rfc2822() . ' – ' . $e->getMessage(), 0, 2000);
            $feed->write();

            throw $e;
        }
    }

    public function export(ICalFeed $forFeed): string
    {
        $host = parse_url(Director::absoluteBaseURL(), PHP_URL_HOST) ?: 'villadina';
        $from = (new DateTimeImmutable('today'))->modify('-30 days')->format('Y-m-d');
        $title = (string) SiteConfig::current_site_config()->Title;
        $stamp = gmdate('Ymd\THis\Z');

        $out = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//' . $this->escape($title) . '//Booking//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->escape($title),
        ];

        $add = function (string $uid, string $start, string $end, string $summary) use (&$out, $stamp): void {
            $out[] = 'BEGIN:VEVENT';
            $out[] = 'UID:' . $uid;
            $out[] = 'DTSTAMP:' . $stamp;
            $out[] = 'DTSTART;VALUE=DATE:' . str_replace('-', '', $start);
            $out[] = 'DTEND;VALUE=DATE:' . str_replace('-', '', $end);
            $out[] = 'SUMMARY:' . $this->escape($summary);
            $out[] = 'TRANSP:OPAQUE';
            $out[] = 'END:VEVENT';
        };

        foreach (Reservation::get()->filter(['Status' => Reservation::BLOCKING, 'Departure:GreaterThanOrEqual' => $from]) as $r) {
            $add("vd-res-{$r->ID}@{$host}", $r->Arrival, $r->Departure, 'Reserved');
        }
        foreach (BlockedPeriod::get()->filter('EndDate:GreaterThanOrEqual', $from) as $b) {
            $add("vd-block-{$b->ID}@{$host}", $b->StartDate, $b->EndDate, 'Not available');
        }
        foreach (ExternalBlock::get()
            ->filter(['EndDate:GreaterThanOrEqual' => $from, 'Feed.Active' => true])
            ->exclude('FeedID', $forFeed->ID) as $b) {
            $add("vd-ext-{$b->ID}@{$host}", $b->StartDate, $b->EndDate, 'Not available');
        }

        $out[] = 'END:VCALENDAR';

        return implode("\r\n", array_map([$this, 'fold'], $out)) . "\r\n";
    }

    private function escape(string $v): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\\;', '\\,', '\\n', '\\n'], $v);
    }

    /**
     * Fold lines longer than 75 octets (RFC 5545 §3.1).
     */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }
        $chunks = [];
        while (strlen($line) > 75) {
            $cut = 75;
            // don't split multibyte characters
            while ($cut > 0 && (ord($line[$cut]) & 0xC0) === 0x80) {
                $cut--;
            }
            $chunks[] = substr($line, 0, $cut);
            $line = ' ' . substr($line, $cut);
        }
        $chunks[] = $line;

        return implode("\r\n", $chunks);
    }

    private function http(): Client
    {
        return $this->http ??= new Client();
    }
}

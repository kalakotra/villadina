<?php

declare(strict_types=1);

namespace App\Booking;

use App\Booking\ICal\ICalSync;
use App\Booking\Model\ICalFeed;
use App\Booking\Service\PriceCalculator;
use App\Booking\Service\StripeGateway;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\i18n\i18n;
use Throwable;

/**
 * Public JSON / feed endpoints.
 *
 *   GET  /booking/availability?from=&to=                     occupied nights
 *   GET  /booking/quote?arrival=&departure=&adults=&children=&infants=&locale=
 *   GET  /booking/ical/{token}.ics                            export for a channel
 *   POST /booking/stripe-webhook                              Stripe events
 */
class BookingController extends Controller
{
    private const MAX_RANGE_DAYS = 550;

    /** Locales accepted by ?locale= (Fluent locales) */
    private static array $allowed_locales = ['hr_HR', 'de_DE', 'en_GB'];

    private static array $allowed_actions = [
        'availability',
        'quote',
        'ical',
        'stripeWebhook',
    ];

    private static array $url_handlers = [
        'availability' => 'availability',
        'quote' => 'quote',
        'ical/$Token' => 'ical',
        'stripe-webhook' => 'stripeWebhook',
    ];

    public function index(HTTPRequest $request): HTTPResponse
    {
        return $this->httpError(404);
    }

    public function availability(HTTPRequest $request): HTTPResponse
    {
        $today = new DateTimeImmutable('today');
        $from = $this->parseDate($request->getVar('from')) ?? $today;
        $to = $this->parseDate($request->getVar('to')) ?? $today->modify('+18 months');

        if ($from < $today) {
            $from = $today;
        }
        if ($to <= $from || $from->diff($to)->days > self::MAX_RANGE_DAYS) {
            $to = $from->modify('+' . self::MAX_RANGE_DAYS . ' days');
        }

        $provider = Injector::inst()->get(AvailabilityProviderInterface::class);

        return $this->json([
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'blocked' => $provider->getBlockedNights($from, $to),
            'maxGuests' => $provider->getMaxGuests(),
        ], 200, 'public, max-age=120');
    }

    public function quote(HTTPRequest $request): HTTPResponse
    {
        $locale = $this->locale($request->getVar('locale'));

        $data = i18n::with_locale($locale, function () use ($request, $locale): array {
            $quote = PriceCalculator::singleton()->quote(
                $this->parseDate($request->getVar('arrival')),
                $this->parseDate($request->getVar('departure')),
                max(0, (int) $request->getVar('adults')),
                max(0, (int) $request->getVar('children')),
                max(0, (int) $request->getVar('infants')),
            );
            $data = $quote->toArray($locale);

            // Availability is part of a valid quote for guests
            if ($quote->isValid() && !Injector::inst()->get(AvailabilityProviderInterface::class)->isAvailable($quote->arrival, $quote->departure)) {
                $data['valid'] = false;
                $data['errors'][] = _t('VDBooking.ErrNotAvailable', 'Sorry, these dates are no longer available.');
            }

            return $data;
        });

        return $this->json($data, 200, 'private, no-store');
    }

    public function ical(HTTPRequest $request): HTTPResponse
    {
        $token = preg_replace('/\.ics$/', '', (string) $request->param('Token'));
        if (!preg_match('/^[a-f0-9]{48}$/', (string) $token)) {
            return $this->httpError(404);
        }

        /** @var ICalFeed|null $feed */
        $feed = ICalFeed::get()->filter(['ExportToken' => $token, 'Active' => true])->first();
        if (!$feed) {
            return $this->httpError(404);
        }

        $response = HTTPResponse::create(Injector::inst()->get(ICalSync::class)->export($feed));
        $response->addHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->addHeader('Content-Disposition', 'inline; filename="villa-dina.ics"');
        $response->addHeader('Cache-Control', 'no-cache');
        $response->addHeader('X-Robots-Tag', 'noindex');

        return $response;
    }

    public function stripeWebhook(HTTPRequest $request): HTTPResponse
    {
        if (!$request->isPOST()) {
            return $this->json(['error' => 'Method not allowed'], 405);
        }

        try {
            Injector::inst()->get(StripeGateway::class)->handleWebhook(
                (string) $request->getBody(),
                (string) $request->getHeader('Stripe-Signature'),
            );
        } catch (\UnexpectedValueException | \Stripe\Exception\SignatureVerificationException $e) {
            return $this->json(['error' => 'Invalid payload or signature'], 400);
        } catch (Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->error('Stripe webhook failed: ' . $e->getMessage());

            return $this->json(['error' => 'Processing failed'], 500); // Stripe retries
        }

        return $this->json(['received' => true]);
    }

    // ---------------------------------------------------------------------

    private function locale(mixed $value): string
    {
        $allowed = static::config()->get('allowed_locales');
        if (is_string($value)) {
            foreach ($allowed as $locale) {
                if (strcasecmp($locale, $value) === 0 || strcasecmp(substr($locale, 0, 2), $value) === 0) {
                    return $locale;
                }
            }
        }

        return i18n::get_locale();
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return DateTimeImmutable::createFromFormat('!Y-m-d', $value) ?: null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function json(array $data, int $status = 200, string $cache = 'no-store'): HTTPResponse
    {
        $response = HTTPResponse::create(
            json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $status,
        );
        $response->addHeader('Content-Type', 'application/json; charset=utf-8');
        $response->addHeader('Cache-Control', $cache);
        $response->addHeader('X-Robots-Tag', 'noindex');

        return $response;
    }
}

<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\Model\Payment;
use App\Booking\Model\Reservation;
use App\Booking\Money;
use RuntimeException;
use SilverStripe\Core\Environment;
use SilverStripe\SiteConfig\SiteConfig;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * Stripe Checkout (card, SEPA Debit, Apple/Google Pay, … — whatever is enabled
 * in the Stripe dashboard; payment methods are chosen dynamically).
 *
 * .env:  STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET
 * Webhook endpoint: https://<site>/booking/stripe-webhook
 * Events: checkout.session.completed, checkout.session.async_payment_succeeded,
 *         checkout.session.async_payment_failed, checkout.session.expired
 */
class StripeGateway
{
    private ?StripeClient $client = null;

    public function __construct(private readonly ReservationService $reservations)
    {
    }

    public function isEnabled(): bool
    {
        return (string) Environment::getEnv('STRIPE_SECRET_KEY') !== '' && class_exists(StripeClient::class);
    }

    /**
     * Creates a pending Payment + Checkout Session and returns the redirect URL.
     */
    public function createCheckout(Reservation $r, string $successUrl, string $cancelUrl): string
    {
        $due = $r->dueNow();
        if (!$due) {
            throw new RuntimeException('Nothing to pay.');
        }

        // Re-use an open session for the same amount (double clicks, back button)
        $existing = $r->Payments()->filter([
            'Method' => 'stripe',
            'Status' => 'pending',
            'Kind' => $due['kind'],
            'Amount' => $due['amount'],
        ])->first();
        if ($existing && $existing->StripeSessionID) {
            $session = $this->client()->checkout->sessions->retrieve($existing->StripeSessionID);
            if ($session->status === 'open' && $session->url) {
                return $session->url;
            }
            $existing->Status = 'cancelled';
            $existing->write();
        }

        $payment = Payment::create([
            'ReservationID' => $r->ID,
            'Kind' => $due['kind'],
            'Method' => 'stripe',
            'Amount' => $due['amount'],
            'Status' => 'pending',
        ]);
        $payment->write();

        $siteTitle = (string) SiteConfig::current_site_config()->Title;

        $session = $this->client()->checkout->sessions->create([
            'mode' => 'payment',
            'customer_email' => $r->Email,
            'client_reference_id' => $r->Reference,
            'locale' => $this->stripeLocale((string) $r->Locale),
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower(Money::CURRENCY),
                    'unit_amount' => $due['amount'],
                    'product_data' => [
                        'name' => sprintf('%s – %s %s', $siteTitle, $r->DueNowKindLabel(), $r->Reference),
                        'description' => sprintf('%s – %s', date('d.m.Y', (int) strtotime($r->Arrival)), date('d.m.Y', (int) strtotime($r->Departure))),
                    ],
                ],
            ]],
            'payment_intent_data' => [
                'description' => $r->Reference . ' ' . $due['kind'],
                'metadata' => ['reservation' => $r->Reference, 'payment_id' => (string) $payment->ID],
            ],
            'metadata' => ['reservation' => $r->Reference, 'payment_id' => (string) $payment->ID],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'expires_at' => time() + 3600 * 23,
        ], [
            'idempotency_key' => 'vd-payment-' . $payment->ID,
        ]);

        $payment->StripeSessionID = $session->id;
        $payment->write();

        return (string) $session->url;
    }

    /**
     * @throws \Stripe\Exception\SignatureVerificationException|\UnexpectedValueException
     */
    public function handleWebhook(string $payload, string $signature): void
    {
        $event = Webhook::constructEvent($payload, $signature, (string) Environment::getEnv('STRIPE_WEBHOOK_SECRET'));

        if (!str_starts_with($event->type, 'checkout.session.')) {
            return;
        }

        /** @var Session $session */
        $session = $event->data->object;
        $payment = $this->findPayment($session);
        if (!$payment) {
            return;
        }

        match ($event->type) {
            'checkout.session.completed' => $session->payment_status === 'paid'
                ? $this->markPaid($payment, $session)
                : null, // async (SEPA): wait for async_payment_succeeded
            'checkout.session.async_payment_succeeded' => $this->markPaid($payment, $session),
            'checkout.session.async_payment_failed' => $this->markStatus($payment, 'failed'),
            'checkout.session.expired' => $payment->Status === 'pending' ? $this->markStatus($payment, 'cancelled') : null,
            default => null,
        };
    }

    private function markPaid(Payment $payment, Session $session): void
    {
        if ($payment->isPaid()) {
            return; // idempotent – Stripe retries webhooks
        }
        $payment->StripePaymentIntentID = is_string($session->payment_intent) ? $session->payment_intent : ($session->payment_intent->id ?? null);
        $this->reservations->recordPayment($payment);
    }

    private function markStatus(Payment $payment, string $status): void
    {
        if ($payment->isPaid()) {
            return;
        }
        $payment->Status = $status;
        $payment->write();
    }

    private function findPayment(Session $session): ?Payment
    {
        $id = (int) ($session->metadata['payment_id'] ?? 0);
        /** @var Payment|null $payment */
        $payment = $id ? Payment::get()->byID($id) : null;

        if ($payment && $payment->StripeSessionID === $session->id) {
            return $payment;
        }

        return Payment::get()->filter('StripeSessionID', $session->id)->first();
    }

    private function client(): StripeClient
    {
        return $this->client ??= new StripeClient((string) Environment::getEnv('STRIPE_SECRET_KEY'));
    }

    private function stripeLocale(string $locale): string
    {
        return match (substr($locale, 0, 2)) {
            'de' => 'de',
            'hr' => 'hr',
            default => 'en',
        };
    }
}

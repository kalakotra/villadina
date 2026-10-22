<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\AvailabilityProviderInterface;
use App\Booking\Model\Payment;
use App\Booking\Model\Reservation;
use DateTimeImmutable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * All reservation state transitions live here.
 */
class ReservationService
{
    use Injectable;

    private static array $dependencies = [
        'availability' => '%$' . AvailabilityProviderInterface::class,
        'calculator' => '%$' . PriceCalculator::class,
        'schedule' => '%$' . PaymentSchedule::class,
        'mailer' => '%$' . BookingMailer::class,
    ];

    public AvailabilityProviderInterface $availability;
    public PriceCalculator $calculator;
    public PaymentSchedule $schedule;
    public BookingMailer $mailer;

    // ---------------------------------------------------------------------
    // Guest request
    // ---------------------------------------------------------------------

    /**
     * @param array<string, mixed> $guest FirstName, LastName, Email, Phone, Street, PostalCode, City, Country, Message
     * @throws BookingException
     */
    public function createRequest(Quote $quote, array $guest, string $locale): Reservation
    {
        if (!$quote->isValid() || !$quote->arrival || !$quote->departure) {
            throw new BookingException(implode(' ', $quote->errors) ?: 'Invalid quote.');
        }
        if (!$this->availability->isAvailable($quote->arrival, $quote->departure)) {
            throw new BookingException(_t('VDBooking.ErrNotAvailable', 'Sorry, these dates are no longer available.'));
        }

        $r = Reservation::create();
        $r->update([
            'Status' => 'requested',
            'Source' => 'website',
            'Arrival' => $quote->arrival->format('Y-m-d'),
            'Departure' => $quote->departure->format('Y-m-d'),
            'Adults' => $quote->adults,
            'Children' => $quote->children,
            'Infants' => $quote->infants,
            'FirstName' => trim((string) ($guest['FirstName'] ?? '')),
            'LastName' => trim((string) ($guest['LastName'] ?? '')),
            'Email' => trim((string) ($guest['Email'] ?? '')),
            'Phone' => trim((string) ($guest['Phone'] ?? '')),
            'Street' => trim((string) ($guest['Street'] ?? '')),
            'PostalCode' => trim((string) ($guest['PostalCode'] ?? '')),
            'City' => trim((string) ($guest['City'] ?? '')),
            'Country' => strtoupper(substr((string) ($guest['Country'] ?? ''), 0, 2)),
            'Message' => trim((string) ($guest['Message'] ?? '')),
            'Locale' => $locale,
            'TermsAcceptedAt' => DBDatetime::now()->Rfc2822(),
            'RecalculatePrice' => false,
        ]);
        $r->applyQuoteAmounts($quote->accommodation, $quote->touristTax, $quote->touristTaxIncluded, $quote->linesForStorage());
        $r->DepositAmount = $quote->deposit;
        $r->BalanceAmount = $quote->balance;
        $r->write();

        $this->mailer->requestReceived($r);
        $this->mailer->ownerNewRequest($r);

        return $r;
    }

    // ---------------------------------------------------------------------
    // Owner actions
    // ---------------------------------------------------------------------

    /**
     * Hold the dates and send the payment link.
     *
     * @throws BookingException
     */
    public function approve(Reservation $r): void
    {
        $this->assertStatus($r, ['requested']);

        DB::get_conn()->withTransaction(function () use ($r): void {
            if (!$this->availability->isAvailable($r->arrivalDate(), $r->departureDate(), (int) $r->ID)) {
                throw new BookingException('Dates are no longer free (another reservation, block or channel booking).');
            }

            $plan = $this->schedule->compute((int) $r->TotalAmount, $r->arrivalDate());

            $r->Status = 'approved';
            $r->DepositAmount = $plan['deposit'];
            $r->BalanceAmount = $plan['balance'];
            $r->DepositDueDate = $plan['depositDue']->format('Y-m-d');
            $r->BalanceDueDate = $plan['balanceDue']?->format('Y-m-d');
            $r->ApprovedAt = DBDatetime::now()->Rfc2822();
            $r->write();
        });

        $this->mailer->approved($r);
    }

    public function decline(Reservation $r, ?string $reason = null): void
    {
        $this->assertStatus($r, ['requested']);
        $r->Status = 'declined';
        if ($reason !== null && $reason !== '') {
            $r->DeclineReason = $reason;
        }
        $r->write();

        $this->mailer->declined($r);
    }

    public function cancel(Reservation $r, ?string $reason = null, bool $notifyGuest = true): void
    {
        $this->assertStatus($r, ['requested', 'approved', 'confirmed', 'paid']);
        $r->Status = 'cancelled';
        $r->CancelledAt = DBDatetime::now()->Rfc2822();
        if ($reason !== null && $reason !== '') {
            $r->DeclineReason = $reason;
        }
        $r->write();

        foreach ($r->Payments()->filter('Status', 'pending') as $p) {
            $p->Status = 'cancelled';
            $p->write();
        }

        if ($notifyGuest) {
            $this->mailer->cancelled($r);
        }
    }

    public function resendPaymentLink(Reservation $r): void
    {
        $this->assertStatus($r, Reservation::PAYABLE);
        $r->Status === 'approved' ? $this->mailer->approved($r) : $this->mailer->balanceReminder($r);
    }

    /**
     * Owner confirms a bank transfer / cash payment of the currently due amount.
     */
    public function recordManualPayment(Reservation $r, string $method = 'bank', ?int $amount = null): Payment
    {
        $due = $r->dueNow();
        if (!$due) {
            throw new BookingException('Nothing is due on this reservation.');
        }

        $payment = Payment::create([
            'ReservationID' => $r->ID,
            'Kind' => $due['kind'],
            'Method' => $method,
            'Amount' => $amount ?? $due['amount'],
        ]);

        return $this->recordPayment($payment);
    }

    // ---------------------------------------------------------------------
    // Payments
    // ---------------------------------------------------------------------

    /**
     * Marks a payment as paid and advances the reservation.
     */
    public function recordPayment(Payment $payment): Payment
    {
        $payment->Status = 'paid';
        $payment->PaidAt = DBDatetime::now()->Rfc2822();
        $payment->write(); // onAfterWrite → syncStatusFromPayments()

        $r = $payment->Reservation();
        foreach ($r->Payments()->filter(['Status' => 'pending', 'Kind' => $payment->Kind])->exclude('ID', $payment->ID) as $other) {
            $other->Status = 'cancelled';
            $other->write();
        }

        $this->mailer->ownerPaymentReceived($r, (int) $payment->Amount, (string) $payment->Method);

        return $payment;
    }

    /**
     * Idempotent: derives status from the sum of paid payments.
     */
    public function syncStatusFromPayments(Reservation $r): void
    {
        $r = Reservation::get()->byID($r->ID) ?? $r; // fresh copy
        if (!in_array($r->Status, ['approved', 'confirmed', 'expired'], true)) {
            return;
        }

        $paid = $r->PaidAmount();
        $before = $r->Status;

        if ($paid >= (int) $r->TotalAmount) {
            $r->Status = 'paid';
        } elseif ($paid >= (int) $r->DepositAmount && $before !== 'confirmed') {
            $r->Status = 'confirmed';
        }

        if ($r->Status === $before) {
            return;
        }

        // Late payment on an expired reservation: only reinstate if still free
        if ($before === 'expired' && !$this->availability->isAvailable($r->arrivalDate(), $r->departureDate(), (int) $r->ID)) {
            $r->Status = 'expired';
            $r->InternalNotes = trim($r->InternalNotes . "\nLate payment received but dates are taken – refund required.");
            $r->write();

            return;
        }

        if (!$r->ConfirmedAt) {
            $r->ConfirmedAt = DBDatetime::now()->Rfc2822();
        }
        $r->write();

        $last = $r->Payments()->filter('Status', 'paid')->sort('PaidAt', 'DESC')->first();
        $this->mailer->paymentReceived($r, $last ? (int) $last->Amount : $paid);
    }

    // ---------------------------------------------------------------------
    // Scheduled maintenance (BookingMaintenanceTask)
    // ---------------------------------------------------------------------

    /**
     * @return array<string, int> counters
     */
    public function runMaintenance(?DateTimeImmutable $today = null): array
    {
        $today ??= new DateTimeImmutable('today');
        $t = $today->format('Y-m-d');
        $reminderDays = max(1, (int) SiteConfig::current_site_config()->BalanceReminderDays ?: 14);
        $stats = ['expired' => 0, 'reminded' => 0, 'overdue' => 0, 'completed' => 0];

        // 1. Approved but deposit not paid in time → release dates
        foreach (Reservation::get()->filter(['Status' => 'approved', 'DepositDueDate:LessThan' => $t]) as $r) {
            if ($r->PaidAmount() >= (int) $r->DepositAmount) {
                $this->syncStatusFromPayments($r);
                continue;
            }
            $r->Status = 'expired';
            $r->write();
            foreach ($r->Payments()->filter('Status', 'pending') as $p) {
                $p->Status = 'cancelled';
                $p->write();
            }
            $this->mailer->expired($r);
            $stats['expired']++;
        }

        // 2. Balance reminder
        $remindFrom = $today->modify("+{$reminderDays} days")->format('Y-m-d');
        foreach (Reservation::get()->filter([
            'Status' => 'confirmed',
            'BalanceReminderSentAt' => null,
            'BalanceDueDate:LessThanOrEqual' => $remindFrom,
            'BalanceDueDate:GreaterThanOrEqual' => $t,
        ]) as $r) {
            if ($r->OutstandingAmount() <= 0) {
                continue;
            }
            $this->mailer->balanceReminder($r);
            $r->BalanceReminderSentAt = DBDatetime::now()->Rfc2822();
            $r->write();
            $stats['reminded']++;
        }

        // 3. Balance overdue → tell the owner (no automatic cancellation)
        foreach (Reservation::get()->filter([
            'Status' => 'confirmed',
            'OverdueNotifiedAt' => null,
            'BalanceDueDate:LessThan' => $t,
        ]) as $r) {
            if ($r->OutstandingAmount() <= 0) {
                continue;
            }
            $this->mailer->ownerBalanceOverdue($r);
            $r->OverdueNotifiedAt = DBDatetime::now()->Rfc2822();
            $r->write();
            $stats['overdue']++;
        }

        // 4. Past stays
        foreach (Reservation::get()->filter(['Status' => 'paid', 'Departure:LessThan' => $t]) as $r) {
            $r->Status = 'completed';
            $r->write();
            $stats['completed']++;
        }

        return $stats;
    }

    /**
     * @param list<string> $allowed
     */
    private function assertStatus(Reservation $r, array $allowed): void
    {
        if (!in_array($r->Status, $allowed, true)) {
            throw new BookingException(sprintf('Not possible in status "%s".', $r->StatusLabel()));
        }
    }
}

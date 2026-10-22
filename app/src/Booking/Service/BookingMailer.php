<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\Admin\BookingAdmin;
use App\Booking\Model\Reservation;
use App\Booking\Money;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\Director;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\i18n\i18n;
use SilverStripe\SiteConfig\SiteConfig;
use Throwable;

/**
 * All booking e-mails share one template (App/Booking/Email/BookingEmail.ss).
 * Guest e-mails are rendered in the guest's locale.
 */
class BookingMailer
{
    public function __construct(private readonly ProformaPdf $proforma = new ProformaPdf())
    {
    }

    // --- Guest ------------------------------------------------------------

    public function requestReceived(Reservation $r): void
    {
        $this->toGuest($r, fn () => [
            'subject' => _t('VDBooking.MailRequestSubject', 'Your request {ref}', ['ref' => $r->Reference]),
            'heading' => _t('VDBooking.MailRequestHeading', 'Thank you for your request'),
            'intro' => _t('VDBooking.MailRequestIntro', 'We have received your booking request and will get back to you within 24 hours. Your dates are not reserved until we confirm.'),
        ]);
    }

    public function approved(Reservation $r): void
    {
        $due = $r->dueNow();
        $attachments = [];
        if (SiteConfig::current_site_config()->BankTransferEnabled() && $due) {
            $attachments[] = [$this->proforma->render($r), $this->proforma->filename($r)];
        }

        $this->toGuest($r, fn () => [
            'subject' => _t('VDBooking.MailApprovedSubject', 'Your booking {ref} – please pay to confirm', ['ref' => $r->Reference]),
            'heading' => _t('VDBooking.MailApprovedHeading', 'Good news – your dates are available!'),
            'intro' => $r->IsFullPaymentUpfront()
                ? _t('VDBooking.MailApprovedIntroFull', 'We are holding the dates for you. Please pay the full amount of {amount} by {date} to confirm your booking.', ['amount' => $r->DueNowFormatted(), 'date' => $this->date($due['due'] ?? null)])
                : _t('VDBooking.MailApprovedIntro', 'We are holding the dates for you. Please pay the deposit of {amount} by {date} to confirm your booking. The balance of {balance} is due by {balanceDate}.', [
                    'amount' => $r->DueNowFormatted(),
                    'date' => $this->date($due['due'] ?? null),
                    'balance' => $r->BalanceFormatted(),
                    'balanceDate' => $this->date($r->BalanceDueDate),
                ]),
            'button' => [$r->PaymentLink(), _t('VDBooking.PayNow', 'Pay now')],
            'outro' => _t('VDBooking.MailApprovedOutro', 'Without payment by the due date the reservation is released automatically.'),
        ], $attachments);
    }

    public function declined(Reservation $r): void
    {
        $this->toGuest($r, fn () => [
            'subject' => _t('VDBooking.MailDeclinedSubject', 'Your request {ref}', ['ref' => $r->Reference]),
            'heading' => _t('VDBooking.MailDeclinedHeading', 'Unfortunately we cannot confirm your request'),
            'intro' => $r->DeclineReason ?: _t('VDBooking.MailDeclinedIntro', 'The requested dates are no longer available. We would be happy to offer you alternative dates.'),
            'summary' => true,
        ]);
    }

    public function paymentReceived(Reservation $r, int $amount): void
    {
        $fully = $r->Status === 'paid';
        $this->toGuest($r, fn () => [
            'subject' => $fully
                ? _t('VDBooking.MailPaidSubject', 'Booking {ref} fully paid', ['ref' => $r->Reference])
                : _t('VDBooking.MailConfirmedSubject', 'Booking {ref} confirmed', ['ref' => $r->Reference]),
            'heading' => $fully
                ? _t('VDBooking.MailPaidHeading', 'Thank you – your stay is fully paid')
                : _t('VDBooking.MailConfirmedHeading', 'Your booking is confirmed!'),
            'intro' => $fully
                ? _t('VDBooking.MailPaidIntro', 'We have received {amount}. We look forward to welcoming you to Villa Dina.', ['amount' => $this->money($amount, $r)])
                : _t('VDBooking.MailConfirmedIntro', 'We have received your deposit of {amount}. The balance of {balance} is due by {date} – we will remind you in time.', [
                    'amount' => $this->money($amount, $r),
                    'balance' => $this->money($r->OutstandingAmount(), $r),
                    'date' => $this->date($r->BalanceDueDate),
                ]),
        ]);
    }

    public function balanceReminder(Reservation $r): void
    {
        $this->toGuest($r, fn () => [
            'subject' => _t('VDBooking.MailReminderSubject', 'Balance for booking {ref}', ['ref' => $r->Reference]),
            'heading' => _t('VDBooking.MailReminderHeading', 'Your holiday is getting closer'),
            'intro' => _t('VDBooking.MailReminderIntro', 'Please pay the balance of {amount} by {date}.', ['amount' => $r->DueNowFormatted(), 'date' => $this->date($r->BalanceDueDate)]),
            'button' => [$r->PaymentLink(), _t('VDBooking.PayNow', 'Pay now')],
        ]);
    }

    public function expired(Reservation $r): void
    {
        $this->toGuest($r, fn () => [
            'subject' => _t('VDBooking.MailExpiredSubject', 'Reservation {ref} released', ['ref' => $r->Reference]),
            'heading' => _t('VDBooking.MailExpiredHeading', 'Your reservation has been released'),
            'intro' => _t('VDBooking.MailExpiredIntro', 'We did not receive the payment in time, so the dates have been released. Please contact us if you would still like to book.'),
        ]);
        $this->toOwner($r, 'Expired (unpaid): ' . $r->Reference, 'The reservation was released because the payment did not arrive in time.');
    }

    public function cancelled(Reservation $r): void
    {
        $this->toGuest($r, fn () => [
            'subject' => _t('VDBooking.MailCancelledSubject', 'Booking {ref} cancelled', ['ref' => $r->Reference]),
            'heading' => _t('VDBooking.MailCancelledHeading', 'Your booking has been cancelled'),
            'intro' => $r->DeclineReason ?: _t('VDBooking.MailCancelledIntro', 'Your booking has been cancelled. If you have any questions, please contact us.'),
        ]);
    }

    // --- Owner ------------------------------------------------------------

    public function ownerNewRequest(Reservation $r): void
    {
        $this->toOwner($r, 'New booking request ' . $r->Reference, 'A new request is waiting for approval.' . ($r->Message ? "\n\nMessage: " . $r->Message : ''));
    }

    public function ownerPaymentReceived(Reservation $r, int $amount, string $method): void
    {
        $this->toOwner($r, sprintf('Payment received %s (%s)', $r->Reference, $method), sprintf('%s received. Status: %s.', $this->money($amount, $r), $r->StatusLabel()));
    }

    public function ownerBalanceOverdue(Reservation $r): void
    {
        $this->toOwner($r, 'Balance overdue ' . $r->Reference, sprintf('The balance of %s was due on %s.', $this->money($r->OutstandingAmount(), $r), $r->BalanceDueDate));
    }

    // --- Internals --------------------------------------------------------

    /**
     * @param callable(): array<string, mixed> $content evaluated in the guest's locale
     * @param list<array{0: string, 1: string}> $attachments [data, filename]
     */
    private function toGuest(Reservation $r, callable $content, array $attachments = []): void
    {
        $locale = $r->Locale ?: i18n::get_locale();

        i18n::with_locale($locale, function () use ($r, $content, $attachments): void {
            $c = $content();
            $this->send($r->Email, (string) $c['subject'], $r, [
                'Heading' => $c['heading'] ?? '',
                'Intro' => $c['intro'] ?? '',
                'Outro' => $c['outro'] ?? '',
                'ButtonLink' => $c['button'][0] ?? '',
                'ButtonText' => $c['button'][1] ?? '',
                'ShowSummary' => $c['summary'] ?? true,
                'IsOwner' => false,
            ], $attachments);
        });
    }

    private function toOwner(Reservation $r, string $subject, string $intro): void
    {
        $to = SiteConfig::current_site_config()->NotifyEmail();
        if ($to === '') {
            return;
        }

        i18n::with_locale('en_GB', function () use ($to, $subject, $intro, $r): void {
            $this->send($to, $subject, $r, [
                'Heading' => $subject,
                'Intro' => $intro,
                'ButtonLink' => $this->cmsLink($r),
                'ButtonText' => 'Open in CMS',
                'ShowSummary' => true,
                'IsOwner' => true,
            ], [], $r->Email);
        });
    }

    /**
     * @param array<string, mixed> $data
     * @param list<array{0: string, 1: string}> $attachments
     */
    private function send(string $to, string $subject, Reservation $r, array $data, array $attachments = [], ?string $replyTo = null): void
    {
        $config = SiteConfig::current_site_config();

        try {
            $email = Email::create()
                ->setTo($to)
                ->setSubject($subject)
                ->setHTMLTemplate('App\\Booking\\Email\\BookingEmail')
                ->setData(array_merge($data, [
                    'Reservation' => $r,
                    'SiteConfig' => $config,
                    'Subject' => $subject,
                ]));

            if ($config->Email) {
                $email->setFrom((string) $config->Email, (string) $config->Title);
            }
            $email->setReplyTo($replyTo ?: ($config->Email ?: $to));

            foreach ($attachments as [$body, $name]) {
                $email->attach($body, $name, 'application/pdf');
            }

            $email->send();
        } catch (Throwable $e) {
            Injector::inst()->get(LoggerInterface::class)->error('Booking mail failed: ' . $e->getMessage(), ['reservation' => $r->ID]);
        }
    }

    private function cmsLink(Reservation $r): string
    {
        return Director::absoluteURL(BookingAdmin::singleton()->getCMSEditLinkForManagedDataObject($r));
    }

    private function money(int $cents, Reservation $r): string
    {
        return Money::format($cents, $r->Locale ?: null);
    }

    private function date(?string $ymd): string
    {
        return $ymd ? date('d.m.Y', (int) strtotime($ymd)) : '';
    }
}

<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\Money;
use DateTimeImmutable;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * 30 % deposit now, balance N days before arrival — or 100 % if already inside that window.
 */
final class PaymentSchedule
{
    /**
     * @return array{deposit: int, balance: int, depositDue: DateTimeImmutable, balanceDue: ?DateTimeImmutable}
     */
    public function compute(int $total, DateTimeImmutable $arrival, ?DateTimeImmutable $today = null): array
    {
        $config = SiteConfig::current_site_config();
        $today ??= new DateTimeImmutable('today');

        $percent = (float) $config->DepositPercent ?: 30.0;
        $balanceDays = max(0, (int) $config->BalanceDaysBefore ?: 60);
        $payDays = max(1, (int) $config->DepositDays ?: 3);

        $balanceDue = $arrival->modify("-{$balanceDays} days");
        $depositDue = $today->modify("+{$payDays} days");
        $lastPossible = $arrival->modify('-1 day');

        if ($balanceDue <= $today) {
            return [
                'deposit' => $total,
                'balance' => 0,
                'depositDue' => min($depositDue, max($today, $lastPossible)),
                'balanceDue' => null,
            ];
        }

        $deposit = min($total, Money::percent($total, $percent));

        return [
            'deposit' => $deposit,
            'balance' => $total - $deposit,
            'depositDue' => min($depositDue, $balanceDue),
            'balanceDue' => $balanceDue,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Booking;

use NumberFormatter;
use SilverStripe\i18n\i18n;

/**
 * All amounts are stored as integer cents (EUR).
 */
final class Money
{
    public const CURRENCY = 'EUR';

    public static function format(int $cents, ?string $locale = null): string
    {
        $locale ??= i18n::get_locale();
        $amount = $cents / 100;

        if (class_exists(NumberFormatter::class)) {
            $formatted = (new NumberFormatter($locale, NumberFormatter::CURRENCY))->formatCurrency($amount, self::CURRENCY);
            if ($formatted !== false) {
                return $formatted;
            }
        }

        return str_starts_with($locale, 'en')
            ? '€' . number_format($amount, 2, '.', ',')
            : number_format($amount, 2, ',', '.') . ' €';
    }

    public static function toCents(float|int|string|null $value): int
    {
        return (int) round(((float) $value) * 100);
    }

    public static function toDecimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    /**
     * Percentage of an amount, rounded to whole cents.
     */
    public static function percent(int $cents, float $percent): int
    {
        return (int) round($cents * $percent / 100);
    }
}

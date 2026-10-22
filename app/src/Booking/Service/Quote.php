<?php

declare(strict_types=1);

namespace App\Booking\Service;

use App\Booking\Money;
use DateTimeImmutable;

/**
 * Immutable price calculation result.
 */
final readonly class Quote
{
    /**
     * @param list<array{label: string, amount: int}> $lines accommodation lines
     * @param list<string> $errors
     */
    public function __construct(
        public ?DateTimeImmutable $arrival,
        public ?DateTimeImmutable $departure,
        public int $nights,
        public int $adults,
        public int $children,
        public int $infants,
        public array $lines,
        public int $accommodation,
        public int $touristTax,
        public bool $touristTaxIncluded,
        public string $touristTaxLabel,
        public int $total,
        public int $deposit,
        public int $balance,
        public ?DateTimeImmutable $balanceDue,
        public array $errors,
    ) {
    }

    /**
     * @param list<string> $errors
     */
    public static function invalid(array $errors, ?DateTimeImmutable $arrival = null, ?DateTimeImmutable $departure = null): self
    {
        return new self($arrival, $departure, 0, 0, 0, 0, [], 0, 0, false, '', 0, 0, 0, null, $errors);
    }

    public function isValid(): bool
    {
        return $this->errors === [] && $this->total > 0;
    }

    public function isFullUpfront(): bool
    {
        return $this->balance === 0;
    }

    /**
     * Lines persisted on the reservation (accommodation + tax).
     *
     * @return list<array{label: string, amount: int}>
     */
    public function linesForStorage(): array
    {
        $lines = $this->lines;
        if ($this->touristTax > 0) {
            $lines[] = ['label' => $this->touristTaxLabel, 'amount' => $this->touristTax];
        }

        return $lines;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(?string $locale = null): array
    {
        $fmt = static fn (int $c): string => Money::format($c, $locale);

        return [
            'valid' => $this->isValid(),
            'errors' => $this->errors,
            'arrival' => $this->arrival?->format('Y-m-d'),
            'departure' => $this->departure?->format('Y-m-d'),
            'nights' => $this->nights,
            'lines' => array_map(
                static fn (array $l): array => ['label' => $l['label'], 'amount' => $fmt($l['amount'])],
                $this->lines,
            ),
            'touristTax' => [
                'label' => $this->touristTaxLabel,
                'amount' => $fmt($this->touristTax),
                'included' => $this->touristTaxIncluded,
                'cents' => $this->touristTax,
            ],
            'total' => $fmt($this->total),
            'totalCents' => $this->total,
            'deposit' => $fmt($this->deposit),
            'balance' => $fmt($this->balance),
            'balanceDue' => $this->balanceDue?->format('Y-m-d'),
            'fullUpfront' => $this->isFullUpfront(),
        ];
    }
}

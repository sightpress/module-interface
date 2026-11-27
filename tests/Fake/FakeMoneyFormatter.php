<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Money\Money;
use SightPress\Module\Money\MoneyFormatter;

/**
 * A few fixed currency formats, as core keeps one per currency: the same output whatever the locale.
 */
final readonly class FakeMoneyFormatter implements MoneyFormatter
{
    public function format(Money $money): string
    {
        return match ($money->currency) {
            'GBP' => '£' . number_format($money->minorUnits / 100, 2),
            'EUR' => number_format($money->minorUnits / 100, 2, ',', '.') . ' €',
            'JPY' => '¥' . number_format($money->minorUnits),
            default => sprintf('%s %s', $money->currency, number_format($money->minorUnits / 100, 2)),
        };
    }
}

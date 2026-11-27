<?php

declare(strict_types=1);

namespace SightPress\Module\Money;

use SightPress\Module\Internal\Guard;

/**
 * An amount of money: whole minor units (pence, cents, yen) and its own ISO 4217 currency, always together (since
 * 0.6). There is no default currency and nothing converts between currencies; format it with
 * {@see MoneyFormatter}, which uses its currency, never the page's locale.
 */
final readonly class Money
{
    /** @var non-empty-string */
    public string $currency;

    /**
     * @param int    $minorUnits the amount in the currency's smallest unit: `1250` GBP is £12.50, `1250` JPY is ¥1,250
     * @param string $currency   ISO 4217 code, for example `GBP`, `EUR` or `JPY`
     */
    public function __construct(public int $minorUnits, string $currency)
    {
        $this->currency = Guard::matches($currency, '/^[A-Z]{3}$/', 'Currency', 'use a three-letter ISO 4217 code in capitals such as "GBP"');
    }

    public function equals(self $other): bool
    {
        return $this->minorUnits === $other->minorUnits && $this->currency === $other->currency;
    }
}

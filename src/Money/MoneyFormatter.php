<?php

declare(strict_types=1);

namespace SightPress\Module\Money;

/**
 * Formats money by its own currency (since 0.6), in the site's container: one fixed format per currency (symbol, its
 * position and spacing, separators and the currency's own number of decimals), the same on every page and in every
 * admin language. `1250 GBP` is always "£12.50".
 *
 * Implemented by core only; modules use it but must not implement it, so methods can be added in a minor release.
 */
interface MoneyFormatter
{
    public function format(Money $money): string;
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use DateTimeImmutable;
use SightPress\Module\Locale\FormatStyle;
use SightPress\Module\Locale\LocaleFormats;
use SightPress\Module\Money\Money;
use SightPress\Module\Money\MoneyFormatter;
use SightPress\Module\Site\SiteLanguages;

/**
 * One line about an event, formatted as core formats its own text: the date in the format locale, the price by its
 * currency, and the language only on a site with more than one.
 */
final readonly class EventSummary
{
    public function __construct(
        private LocaleFormats $formats,
        private MoneyFormatter $money,
        private SiteLanguages $languages,
    ) {
    }

    public function line(string $title, DateTimeImmutable $startsAt, Money $price, string $locale): string
    {
        $line = sprintf('%s, %s, %s', $title, $this->formats->date($startsAt, FormatStyle::Long), $this->money->format($price));

        return $this->languages->isMultilingual() ? sprintf('%s (%s)', $line, $locale) : $line;
    }
}

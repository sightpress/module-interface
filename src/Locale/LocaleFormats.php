<?php

declare(strict_types=1);

namespace SightPress\Module\Locale;

use DateTimeInterface;
use DateTimeZone;

/**
 * Dates, times, calendar names and numbers in the format locale (since 0.6), in the site's container: the locale the
 * person chose where there is a choice (the page's language on the public site; the person's admin language in the
 * admin, emails and text messages), otherwise the default locale. Money is never formatted here: use
 * {@see \SightPress\Module\Money\MoneyFormatter}.
 *
 * Machine dates (structured data, sitemaps, CSV) stay ISO 8601 and don't use this.
 *
 * Implemented by core only; modules use it but must not implement it, so methods can be added in a minor release.
 */
interface LocaleFormats
{
    /**
     * The format locale (BCP 47).
     *
     * @return non-empty-string
     */
    public function locale(): string;

    /**
     * The time zone dates and times are shown in.
     */
    public function timeZone(): DateTimeZone;

    /**
     * The same service for another locale, for example for an email to someone with another admin language.
     */
    public function withLocale(string $locale): LocaleFormats;

    public function date(DateTimeInterface $value, FormatStyle $style = FormatStyle::Long): string;

    public function time(DateTimeInterface $value, FormatStyle $style = FormatStyle::Short): string;

    public function dateTime(
        DateTimeInterface $value,
        FormatStyle $dateStyle = FormatStyle::Medium,
        FormatStyle $timeStyle = FormatStyle::Short,
    ): string;

    /**
     * @param int<1, 12> $month
     * @param bool       $standAlone the form used on its own (a heading), not inside a date; some languages differ
     */
    public function monthName(int $month, NameWidth $width = NameWidth::Wide, bool $standAlone = true): string;

    /**
     * @param int<1, 7> $isoDay 1 for Monday … 7 for Sunday
     * @param bool      $standAlone the form used on its own (a column heading), not inside a date
     */
    public function weekdayName(int $isoDay, NameWidth $width = NameWidth::Wide, bool $standAlone = true): string;

    /**
     * @return int<1, 7> 1 for Monday (`en-GB`) … 7 for Sunday (`en-US`, `pt-BR`)
     */
    public function firstDayOfWeek(): int;

    /**
     * @param int|null $fractionDigits digits after the decimal separator; null for as many as the value needs
     */
    public function number(int|float $value, ?int $fractionDigits = null): string;

    /**
     * @param float $ratio `0.25` gives "25%"
     */
    public function percent(float $ratio, int $fractionDigits = 0): string;

    /**
     * A file size in bytes, kilobytes, megabytes or gigabytes, for example "2.5 MB".
     */
    public function fileSize(int $bytes): string;
}

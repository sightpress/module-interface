<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use DateTimeInterface;
use DateTimeZone;
use SightPress\Module\Locale\FormatStyle;
use SightPress\Module\Locale\LocaleFormats;
use SightPress\Module\Locale\NameWidth;

/**
 * British English formats without `intl`, enough for contract tests. Other locales only change {@see locale()}.
 */
final readonly class FakeLocaleFormats implements LocaleFormats
{
    /**
     * @param non-empty-string $locale
     */
    public function __construct(private string $locale = 'en-GB', private DateTimeZone $timeZone = new DateTimeZone('Europe/London'))
    {
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function timeZone(): DateTimeZone
    {
        return $this->timeZone;
    }

    public function withLocale(string $locale): self
    {
        return new self($locale === '' ? 'en-GB' : $locale, $this->timeZone);
    }

    public function date(DateTimeInterface $value, FormatStyle $style = FormatStyle::Long): string
    {
        $local = \DateTimeImmutable::createFromInterface($value)->setTimezone($this->timeZone);

        return $local->format(match ($style) {
            FormatStyle::Full => 'l j F Y',
            FormatStyle::Long => 'j F Y',
            FormatStyle::Medium => 'j M Y',
            FormatStyle::Short => 'd/m/Y',
        });
    }

    public function time(DateTimeInterface $value, FormatStyle $style = FormatStyle::Short): string
    {
        $local = \DateTimeImmutable::createFromInterface($value)->setTimezone($this->timeZone);

        return $local->format($style === FormatStyle::Short ? 'H:i' : 'H:i:s');
    }

    public function dateTime(DateTimeInterface $value, FormatStyle $dateStyle = FormatStyle::Medium, FormatStyle $timeStyle = FormatStyle::Short): string
    {
        return $this->date($value, $dateStyle) . ', ' . $this->time($value, $timeStyle);
    }

    public function monthName(int $month, NameWidth $width = NameWidth::Wide, bool $standAlone = true): string
    {
        $name = new \DateTimeImmutable(sprintf('2026-%02d-01', $month))->format('F');

        return self::shorten($name, $width);
    }

    public function weekdayName(int $isoDay, NameWidth $width = NameWidth::Wide, bool $standAlone = true): string
    {
        // 2026-11-23 is a Monday.
        $name = new \DateTimeImmutable('2026-11-23')->modify(sprintf('+%d days', $isoDay - 1))->format('l');

        return self::shorten($name, $width);
    }

    public function firstDayOfWeek(): int
    {
        return 1;
    }

    public function number(int|float $value, ?int $fractionDigits = null): string
    {
        return number_format($value, $fractionDigits ?? (is_int($value) ? 0 : 2));
    }

    public function percent(float $ratio, int $fractionDigits = 0): string
    {
        return number_format($ratio * 100, $fractionDigits) . '%';
    }

    public function fileSize(int $bytes): string
    {
        return $bytes < 1_000_000 ? sprintf('%s KB', number_format($bytes / 1000, 1)) : sprintf('%s MB', number_format($bytes / 1_000_000, 1));
    }

    private static function shorten(string $name, NameWidth $width): string
    {
        return match ($width) {
            NameWidth::Wide => $name,
            NameWidth::Abbreviated => substr($name, 0, 3),
            NameWidth::Narrow => substr($name, 0, 1),
        };
    }
}

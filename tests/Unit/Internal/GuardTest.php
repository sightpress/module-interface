<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Internal;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use stdClass;

#[CoversClass(Guard::class)]
final class GuardTest extends TestCase
{
    public function testNotBlank(): void
    {
        self::assertSame(' a ', Guard::notBlank(' a ', 'Field'));

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field must not be empty.');
        Guard::notBlank(" \t", 'Field');
    }

    public function testMatches(): void
    {
        self::assertSame('abc', Guard::matches('abc', '/^[a-z]+$/', 'Field', 'letters only'));

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field is not valid ("ab1"): letters only.');
        Guard::matches('ab1', '/^[a-z]+$/', 'Field', 'letters only');
    }

    public function testMatchesRefusesEmptyEvenIfThePatternAllowsIt(): void
    {
        $this->expectException(InvalidValueException::class);
        Guard::matches('', '/^.*$/', 'Field', 'anything');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function locales(): iterable
    {
        foreach (['en', 'en-GB', 'pt-PT', 'zh-Hant', 'zh-Hant-TW', 'es-419', 'ast'] as $locale) {
            yield $locale => [$locale];
        }
    }

    #[DataProvider('locales')]
    public function testLocale(string $locale): void
    {
        self::assertSame($locale, Guard::locale($locale, 'Locale'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function notLocales(): iterable
    {
        foreach (['', 'EN', 'en_GB', 'en-gb', 'pt-pt', 'zh-hant', 'english', 'en-GB-oxendict', 'e'] as $locale) {
            yield $locale === '' ? 'empty' : $locale => [$locale];
        }
    }

    #[DataProvider('notLocales')]
    public function testLocaleRefusesOtherTags(string $locale): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage(sprintf('Locale is not valid ("%s"): use a language tag such as "en-GB"', $locale));

        Guard::locale($locale, 'Locale');
    }

    public function testNotNegative(): void
    {
        self::assertSame(0, Guard::notNegative(0, 'Count'));

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Count must not be negative (-3).');
        Guard::notNegative(-3, 'Count');
    }

    public function testUtc(): void
    {
        $lisbon = new DateTimeImmutable('2026-07-01T12:00:00', new DateTimeZone('Europe/Lisbon'));

        $utc = Guard::utc($lisbon);

        self::assertSame('2026-07-01T11:00:00+00:00', $utc->format(DATE_ATOM));
        self::assertSame('UTC', $utc->getTimezone()->getName());
        self::assertSame('Europe/Lisbon', $lisbon->getTimezone()->getName());
    }

    public function testUnique(): void
    {
        Guard::unique(['a', 'b'], 'Field');

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field has duplicates: a, b.');
        Guard::unique(['a', 'b', 'a', 'c', 'b'], 'Field');
    }

    public function testListOf(): void
    {
        $object = new stdClass();
        self::assertSame([$object], Guard::listOf([$object], stdClass::class, 'Field'));
    }

    public function testListOfRefusesAMap(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field must be a list.');
        Guard::listOf(['a' => new stdClass()], stdClass::class, 'Field');
    }

    public function testListOfRefusesOtherTypes(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field[1] must be an instance of stdClass.');
        Guard::listOf([new stdClass(), 'x'], stdClass::class, 'Field');
    }

    public function testListOfStrings(): void
    {
        self::assertSame(['a', ''], Guard::listOfStrings(['a', ''], 'Field'));
    }

    public function testListOfStringsRefusesAMap(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field must be a list.');
        Guard::listOfStrings([1 => 'a'], 'Field');
    }

    public function testListOfStringsRefusesOtherTypes(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field[0] must be a string.');
        Guard::listOfStrings([1], 'Field');
    }

    public function testListOfIds(): void
    {
        self::assertSame([0, 4], Guard::listOfIds([0, 4], 'Field'));
    }

    public function testListOfIdsRefusesAMap(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field must be a list.');
        Guard::listOfIds(['a' => 1], 'Field');
    }

    public function testListOfIdsRefusesNegativeOrOtherValues(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field[1] must be an id (a whole number, 0 or more).');
        Guard::listOfIds([1, -1], 'Field');
    }

    public function testTemplateContextStatusAndCountry(): void
    {
        self::assertSame('@acme_events/admin/list.html.twig', Guard::template('@acme_events/admin/list.html.twig'));
        self::assertSame(['events' => [], '_x1' => 1], Guard::context(['events' => [], '_x1' => 1]));
        self::assertSame(200, Guard::status(200));
        self::assertSame(599, Guard::status(599));
        self::assertSame('GB', Guard::country('gb'));
    }

    public function testStatusRefusesInformationalAndRedirects(): void
    {
        $this->expectException(InvalidValueException::class);
        Guard::status(199);
    }

    public function testStatusRefusesRedirects(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('HTTP status must be 2xx, 4xx or 5xx (301)');
        Guard::status(301);
    }

    public function testContextRefusesNamesThatAreNotWords(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Template variable names must be words such as "events" ("a-b").');
        Guard::context(['a-b' => 1]);
    }
}

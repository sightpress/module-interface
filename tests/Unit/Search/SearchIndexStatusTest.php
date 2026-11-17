<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Search;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Search\SearchIndexStatus;

#[CoversClass(SearchIndexStatus::class)]
#[UsesClass(Guard::class)]
final class SearchIndexStatusTest extends TestCase
{
    public function testFields(): void
    {
        $empty = new SearchIndexStatus('en-GB', 0);
        self::assertSame('en-GB', $empty->locale);
        self::assertSame(0, $empty->documents);
        self::assertNull($empty->lastIndexedAt);

        $status = new SearchIndexStatus('pt-PT', 12, new DateTimeImmutable('2026-11-17T10:00:00+01:00'));
        self::assertSame(12, $status->documents);
        self::assertSame('2026-11-17T09:00:00+00:00', $status->lastIndexedAt?->format(DATE_ATOM));
    }

    public function testRefusesANegativeCount(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Number of documents in "en-GB" must not be negative (-1).');

        new SearchIndexStatus('en-GB', -1);
    }

    public function testRefusesABadLocale(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Search index status locale is not valid ("")');

        new SearchIndexStatus('', 1);
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Search;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Search\SearchPerformed;

#[CoversClass(SearchPerformed::class)]
#[UsesClass(Guard::class)]
final class SearchPerformedTest extends TestCase
{
    public function testNormalisesTheQuery(): void
    {
        $event = new SearchPerformed("  ÉVORA\n\u{00A0} Fado  ", 'pt-PT', 0);

        self::assertSame('évora fado', $event->query);
        self::assertSame('pt-PT', $event->locale);
        self::assertSame(0, $event->resultCount);
    }

    /**
     * @return iterable<string, array{string, string, int, string}>
     */
    public static function invalidEvents(): iterable
    {
        yield 'blank query' => ['  ', 'en-GB', 0, 'Searched text must not be empty.'];
        yield 'invalid UTF-8' => ["\xff", 'en-GB', 0, 'Searched text must not be empty.'];
        yield 'bad locale' => ['fado', 'english', 0, 'Searched locale is not valid ("english")'];
        yield 'negative count' => ['fado', 'en-GB', -2, 'Number of search results must not be negative (-2).'];
    }

    #[DataProvider('invalidEvents')]
    public function testRefusesInvalidEvents(string $query, string $locale, int $count, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        new SearchPerformed($query, $locale, $count);
    }
}

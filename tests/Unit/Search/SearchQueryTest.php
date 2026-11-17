<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Search;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Search\SearchQuery;
use SightPress\Module\Search\SearchVisibility;

#[CoversClass(SearchQuery::class)]
#[UsesClass(Guard::class)]
final class SearchQueryTest extends TestCase
{
    public function testDefaults(): void
    {
        $query = new SearchQuery('  fado night ', 'en-GB');

        self::assertSame('fado night', $query->text);
        self::assertSame('en-GB', $query->locale);
        self::assertFalse($query->includeMembersOnly);
        self::assertSame(1, $query->page);
        self::assertSame(10, $query->perPage);
        self::assertSame(0, $query->offset());
        self::assertTrue($query->allows(SearchVisibility::Public));
        self::assertFalse($query->allows(SearchVisibility::Members));
    }

    public function testMembersAndPaging(): void
    {
        $query = new SearchQuery(str_repeat('é', SearchQuery::MAX_LENGTH), 'pt-PT', true, 3, SearchQuery::MAX_PER_PAGE);

        self::assertTrue($query->allows(SearchVisibility::Members));
        self::assertSame(100, $query->offset());
    }

    /**
     * @return iterable<string, array{string, string, int, int, string}>
     */
    public static function invalidQueries(): iterable
    {
        yield 'blank text' => [" \t", 'en-GB', 1, 10, 'Search text must not be empty.'];
        yield 'too long' => [str_repeat('a', 201), 'en-GB', 1, 10, 'Search text must be at most 200 characters.'];
        yield 'bad locale' => ['fado', 'EN', 1, 10, 'Search locale is not valid ("EN")'];
        yield 'page 0' => ['fado', 'en-GB', 0, 10, 'Results page must be 1 or more (0).'];
        yield 'no results per page' => ['fado', 'en-GB', 1, 0, 'Results per page must be between 1 and 50 (0).'];
        yield 'too many per page' => ['fado', 'en-GB', 1, 51, 'Results per page must be between 1 and 50 (51).'];
    }

    #[DataProvider('invalidQueries')]
    public function testRefusesInvalidQueries(string $text, string $locale, int $page, int $perPage, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        new SearchQuery($text, $locale, page: $page, perPage: $perPage);
    }
}

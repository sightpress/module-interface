<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Search;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Search\SearchDocument;
use SightPress\Module\Search\SearchHit;
use SightPress\Module\Search\SearchQuery;
use SightPress\Module\Search\SearchResults;
use SightPress\Module\Search\SearchVisibility;

#[CoversClass(SearchResults::class)]
#[UsesClass(Guard::class)]
#[UsesClass(SearchDocument::class)]
#[UsesClass(SearchHit::class)]
#[UsesClass(SearchQuery::class)]
final class SearchResultsTest extends TestCase
{
    public function testEmptyResults(): void
    {
        $query = new SearchQuery('fado', 'en-GB');
        $results = new SearchResults($query);

        self::assertSame($query, $results->query);
        self::assertSame([], $results->hits);
        self::assertSame(0, $results->total);
        self::assertTrue($results->matchedAllTerms);
        self::assertTrue($results->isEmpty());
        self::assertSame(0, $results->pageCount());
    }

    public function testAPageOfResults(): void
    {
        $query = new SearchQuery('fado', 'en-GB', true, 2, 2);
        $hits = [self::hit('1'), self::hit('2', SearchVisibility::Members)];

        $results = new SearchResults($query, $hits, 5, false);

        self::assertSame($hits, $results->hits);
        self::assertSame(5, $results->total);
        self::assertFalse($results->matchedAllTerms);
        self::assertFalse($results->isEmpty());
        self::assertSame(3, $results->pageCount());
    }

    public function testRefusesMoreHitsThanAPageHolds(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('A page of results holds at most 1 hits (got 2).');

        new SearchResults(new SearchQuery('fado', 'en-GB', perPage: 1), [self::hit('1'), self::hit('2')], 2);
    }

    public function testRefusesATotalBelowTheResultsSoFar(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The total number of results (2) is lower than the results up to this page (3).');

        new SearchResults(new SearchQuery('fado', 'en-GB', page: 2, perPage: 2), [self::hit('3')], 2);
    }

    public function testRefusesANegativeTotal(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Total number of results must not be negative (-1).');

        new SearchResults(new SearchQuery('fado', 'en-GB'), [], -1);
    }

    public function testRefusesSomethingOtherThanHits(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Search hits[0] must be an instance of');

        /** @phpstan-ignore argument.type (testing the run-time check) */
        new SearchResults(new SearchQuery('fado', 'en-GB'), ['page:1'], 1);
    }

    public function testRefusesHitsInAnotherLocale(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Search hit "page:1:en-GB" is not in the query\'s locale "pt-PT".');

        new SearchResults(new SearchQuery('fado', 'pt-PT'), [self::hit('1')], 1);
    }

    public function testRefusesMembersOnlyHitsForAnonymousQueries(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Search hit "page:1:en-GB" is for members only and must not be shown to this query.');

        new SearchResults(new SearchQuery('fado', 'en-GB'), [self::hit('1', SearchVisibility::Members)], 1);
    }

    private static function hit(string $ref, SearchVisibility $visibility = SearchVisibility::Public): SearchHit
    {
        return new SearchHit(new SearchDocument(
            'page',
            $ref,
            'en-GB',
            '/p' . $ref,
            'Page ' . $ref,
            new DateTimeImmutable('2026-01-01'),
            visibility: $visibility,
        ), 1.0);
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Contract;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Search\SearchDocument;
use SightPress\Module\Search\SearchPerformed;
use SightPress\Module\Search\SearchQuery;
use SightPress\Module\Search\SearchVisibility;
use SightPress\Module\Tests\Fake\Labs\InMemorySearchBackend;
use SightPress\Module\Tests\Fake\Module\EventSearchProvider;

/**
 * Contract test: a module's documents go through a back-end written only against this package, and results never
 * cross locales or visibility.
 */
#[CoversNothing]
final class SearchContractTest extends TestCase
{
    private InMemorySearchBackend $backend;

    protected function setUp(): void
    {
        $provider = new EventSearchProvider();
        $this->backend = new InMemorySearchBackend();
        foreach (['en-GB', 'pt-PT'] as $locale) {
            foreach ($provider->documents($locale) as $document) {
                $this->backend->index($document);
            }
        }
    }

    public function testProviderGivesPublishedDocumentsPerLocale(): void
    {
        $provider = new EventSearchProvider();

        $keys = array_map(static fn (SearchDocument $d): string => $d->key(), [...$provider->documents('en-GB')]);
        self::assertSame(['acme.events:fado:en-GB', 'acme.events:workshop:en-GB'], $keys);
        self::assertNull($provider->document('workshop', 'pt-PT'));
        self::assertSame(['Events'], $provider->document('fado', 'pt-PT')?->breadcrumb);
    }

    public function testAnonymousVisitorsOnlyFindPublicDocuments(): void
    {
        $results = $this->backend->query(new SearchQuery('screen readers', 'en-GB'));

        self::assertTrue($results->isEmpty());
        self::assertSame(0, $results->pageCount());
    }

    public function testMembersAlsoFindMembersOnlyDocuments(): void
    {
        $results = $this->backend->query(new SearchQuery('screen read', 'en-GB', includeMembersOnly: true));

        self::assertSame(1, $results->total);
        self::assertTrue($results->matchedAllTerms);
        [$hit] = $results->hits;
        self::assertSame(SearchVisibility::Members, $hit->document->visibility);
        self::assertSame(['screen', 'reader', 'readers'], $hit->matchedWords);
    }

    public function testResultsStayInTheQueryLocale(): void
    {
        $results = $this->backend->query(new SearchQuery('fado', 'pt-PT'));

        self::assertSame(['/pt-pt/eventos/fado'], array_map(static fn ($h): string => $h->document->path, $results->hits));
    }

    public function testFallsBackToAnyWordAndSaysSo(): void
    {
        $results = $this->backend->query(new SearchQuery('fado opera', 'en-GB'));

        self::assertFalse($results->matchedAllTerms);
        self::assertSame(1, $results->total);
    }

    public function testPagesThroughResults(): void
    {
        $this->backend->index(new SearchDocument('page', '1', 'en-GB', '/fado-history', 'Fado history', new DateTimeImmutable('2026-01-01')));

        $first = $this->backend->query(new SearchQuery('fado', 'en-GB', perPage: 1));
        $second = $this->backend->query(new SearchQuery('fado', 'en-GB', page: 2, perPage: 1));

        self::assertSame(2, $first->pageCount());
        self::assertSame('acme.events:fado:en-GB', $first->hits[0]->document->key());
        self::assertSame('page:1:en-GB', $second->hits[0]->document->key());
    }

    public function testRemovesAndClearsDocumentsAndReportsStatus(): void
    {
        self::assertSame(
            [['en-GB', 2], ['pt-PT', 1]],
            array_map(static fn ($s): array => [$s->locale, $s->documents], $this->backend->status()),
        );
        self::assertEquals(new DateTimeImmutable('2026-11-17T09:00:00Z'), $this->backend->status()[0]->lastIndexedAt);

        $this->backend->remove('acme.events', 'fado', 'pt-PT');
        self::assertSame(['en-GB'], array_map(static fn ($s): string => $s->locale, $this->backend->status()));

        $this->backend->remove('acme.events', 'fado');
        $this->backend->clear('en-GB');
        self::assertSame([], $this->backend->status());

        $this->backend->index(new EventSearchProvider()->document('fado', 'en-GB') ?? self::fail());
        $this->backend->clear();
        self::assertSame([], $this->backend->status());
    }

    public function testSearchPerformedCarriesTheNormalisedQueryOnly(): void
    {
        $event = new SearchPerformed("  Fado \t Night ", 'en-GB', 1);

        self::assertSame('fado night', $event->query);
        self::assertSame(['query', 'locale', 'resultCount'], array_keys(get_object_vars($event)));
    }
}

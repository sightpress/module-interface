<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Labs;

use DateTimeImmutable;
use SightPress\Module\Search\SearchBackend;
use SightPress\Module\Search\SearchDocument;
use SightPress\Module\Search\SearchHit;
use SightPress\Module\Search\SearchIndexStatus;
use SightPress\Module\Search\SearchQuery;
use SightPress\Module\Search\SearchResults;

/**
 * A tiny back-end: all words must match (the last one as a prefix), falling back to any word; title words count
 * three times.
 */
final class InMemorySearchBackend implements SearchBackend
{
    /** @var array<string, SearchDocument> by key */
    private array $documents = [];

    /** @var array<string, DateTimeImmutable> by locale */
    private array $lastIndexedAt = [];

    public function __construct(private readonly DateTimeImmutable $now = new DateTimeImmutable('2026-11-17T09:00:00Z'))
    {
    }

    public function index(SearchDocument $document): void
    {
        $this->documents[$document->key()] = $document;
        $this->lastIndexedAt[$document->locale] = $this->now;
    }

    public function remove(string $source, string $ref, ?string $locale = null): void
    {
        $this->documents = array_filter(
            $this->documents,
            static fn (SearchDocument $d): bool => $d->source !== $source || $d->ref !== $ref
                || ($locale !== null && $d->locale !== $locale),
        );
    }

    public function clear(?string $locale = null): void
    {
        $this->documents = $locale === null ? [] : array_filter(
            $this->documents,
            static fn (SearchDocument $d): bool => $d->locale !== $locale,
        );
    }

    public function query(SearchQuery $query): SearchResults
    {
        $terms = self::words($query->text);
        $candidates = array_filter(
            $this->documents,
            static fn (SearchDocument $d): bool => $d->locale === $query->locale && $query->allows($d->visibility),
        );

        $hits = $this->match($candidates, $terms, true);
        $matchedAll = $hits !== [];
        if (!$matchedAll) {
            $hits = $this->match($candidates, $terms, false);
        }
        usort($hits, static fn (SearchHit $a, SearchHit $b): int => [$b->score, $a->document->key()] <=> [$a->score, $b->document->key()]);

        return new SearchResults(
            $query,
            array_slice($hits, $query->offset(), $query->perPage),
            count($hits),
            $matchedAll || $hits === [],
        );
    }

    public function status(): array
    {
        $counts = array_count_values(array_map(static fn (SearchDocument $d): string => $d->locale, $this->documents));
        ksort($counts);

        return array_values(array_map(
            fn (string $locale, int $count): SearchIndexStatus => new SearchIndexStatus($locale, $count, $this->lastIndexedAt[$locale] ?? null),
            array_keys($counts),
            $counts,
        ));
    }

    /**
     * @param array<string, SearchDocument> $documents
     * @param list<string>                  $terms
     *
     * @return list<SearchHit>
     */
    private function match(array $documents, array $terms, bool $all): array
    {
        $hits = [];
        foreach ($documents as $document) {
            $title = self::words($document->title);
            $words = array_merge($title, self::words($document->summary . ' ' . $document->body));
            $score = 0.0;
            $matchedTerms = 0;
            $matchedWords = [];
            foreach ($terms as $index => $term) {
                $last = $index === array_key_last($terms);
                $found = array_filter($words, static fn (string $w): bool => $w === $term || ($last && str_starts_with($w, $term)));
                if ($found !== []) {
                    $matchedTerms++;
                    $score += count($found) + 2 * count(array_intersect($found, $title));
                    $matchedWords = array_merge($matchedWords, $found);
                }
            }
            if ($matchedTerms > 0 && (!$all || $matchedTerms === count($terms))) {
                $hits[] = new SearchHit($document, $score, array_values(array_unique($matchedWords)));
            }
        }

        return $hits;
    }

    /**
     * @return list<non-empty-string>
     */
    private static function words(string $text): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text));

        return $words === false ? [] : array_values(array_filter($words, static fn (string $w): bool => $w !== ''));
    }
}

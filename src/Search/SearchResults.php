<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One page of results for a {@see SearchQuery}, best first.
 */
final readonly class SearchResults
{
    /** @var list<SearchHit> */
    public array $hits;

    public int $total;

    /**
     * @param SearchQuery     $query           the query these results answer
     * @param list<SearchHit> $hits            this page's results, best first; at most {@see SearchQuery::$perPage},
     *                                         each in the query's locale and visible to the query
     * @param int             $total           results on every page together
     * @param bool            $matchedAllTerms false when no document had every word, so these results match any
     *                                         of them; the results page says so
     */
    public function __construct(
        public SearchQuery $query,
        array $hits = [],
        int $total = 0,
        public bool $matchedAllTerms = true,
    ) {
        $this->hits = Guard::listOf($hits, SearchHit::class, 'Search hits');
        $this->total = Guard::notNegative($total, 'Total number of results');

        if (count($this->hits) > $query->perPage) {
            throw new InvalidValueException(sprintf(
                'A page of results holds at most %d hits (got %d).',
                $query->perPage,
                count($this->hits),
            ));
        }
        if ($query->offset() + count($this->hits) > $total) {
            throw new InvalidValueException(sprintf(
                'The total number of results (%d) is lower than the results up to this page (%d).',
                $total,
                $query->offset() + count($this->hits),
            ));
        }
        foreach ($this->hits as $hit) {
            if ($hit->document->locale !== $query->locale) {
                throw new InvalidValueException(sprintf(
                    'Search hit "%s" is not in the query\'s locale "%s".',
                    $hit->document->key(),
                    $query->locale,
                ));
            }
            if (!$query->allows($hit->document->visibility)) {
                throw new InvalidValueException(sprintf(
                    'Search hit "%s" is for members only and must not be shown to this query.',
                    $hit->document->key(),
                ));
            }
        }
    }

    /**
     * Number of pages of results; 0 when there are none.
     */
    public function pageCount(): int
    {
        return (int) ceil($this->total / $this->query->perPage);
    }

    public function isEmpty(): bool
    {
        return $this->total === 0;
    }
}

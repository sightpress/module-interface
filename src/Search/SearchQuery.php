<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * What a visitor searched for, in which locale, what they may see, and which page of results they want.
 *
 * Core builds it from the request and checks the viewer: anonymous visitors only ever get public documents.
 */
final readonly class SearchQuery
{
    /** Longest query text, in characters. Core shortens longer input before it builds a query. */
    public const int MAX_LENGTH = 200;

    /** Most results on one page of results. */
    public const int MAX_PER_PAGE = 50;

    /** @var non-empty-string */
    public string $text;

    /** @var non-empty-string */
    public string $locale;

    /** @var positive-int */
    public int $page;

    /** @var positive-int */
    public int $perPage;

    /**
     * @param string $text               the words searched for, as typed (trimmed); at most {@see MAX_LENGTH}
     *                                   characters. Back-ends do their own text analysis
     * @param string $locale             only documents in this locale are searched (BCP 47 tag)
     * @param bool   $includeMembersOnly whether documents with {@see SearchVisibility::Members} may be returned:
     *                                   true only for people signed in to the site
     * @param int    $page               which page of results, from 1
     * @param int    $perPage            results per page, 1 to {@see MAX_PER_PAGE}
     */
    public function __construct(
        string $text,
        string $locale,
        public bool $includeMembersOnly = false,
        int $page = 1,
        int $perPage = 10,
    ) {
        $text = trim($text);
        $this->text = Guard::notBlank($text, 'Search text');
        if (mb_strlen($text) > self::MAX_LENGTH) {
            throw new InvalidValueException(sprintf('Search text must be at most %d characters.', self::MAX_LENGTH));
        }
        $this->locale = Guard::locale($locale, 'Search locale');
        if ($page < 1) {
            throw new InvalidValueException(sprintf('Results page must be 1 or more (%d).', $page));
        }
        if ($perPage < 1 || $perPage > self::MAX_PER_PAGE) {
            throw new InvalidValueException(sprintf(
                'Results per page must be between 1 and %d (%d).',
                self::MAX_PER_PAGE,
                $perPage,
            ));
        }
        $this->page = $page;
        $this->perPage = $perPage;
    }

    /**
     * How many results come before this page.
     */
    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * Whether a document in this query's locale may be shown to the person searching.
     */
    public function allows(SearchVisibility $visibility): bool
    {
        return $visibility === SearchVisibility::Public || $this->includeMembersOnly;
    }
}

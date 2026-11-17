<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

use SightPress\Module\Internal\Guard;

/**
 * Event dispatched by core after each site search, so other modules (analytics) can count searches. Core's search
 * module stores no queries itself.
 *
 * It carries no information about who searched. Listeners that store the text must drop anything that looks like
 * personal data (email addresses, phone numbers, long numbers) first.
 */
final readonly class SearchPerformed
{
    /** @var non-empty-string */
    public string $query;

    /** @var non-empty-string */
    public string $locale;

    public int $resultCount;

    /**
     * @param string $query       the search text; stored trimmed, lower-cased and with each run of white space
     *                            made one space
     * @param string $locale      the locale searched (BCP 47 tag)
     * @param int    $resultCount results on every page together
     */
    public function __construct(string $query, string $locale, int $resultCount)
    {
        $this->query = Guard::notBlank(
            mb_strtolower(trim((string) preg_replace('/[\s\p{Z}]+/u', ' ', $query))),
            'Searched text',
        );
        $this->locale = Guard::locale($locale, 'Searched locale');
        $this->resultCount = Guard::notNegative($resultCount, 'Number of search results');
    }
}

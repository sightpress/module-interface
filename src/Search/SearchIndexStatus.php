<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

use DateTimeImmutable;
use SightPress\Module\Internal\Guard;

/**
 * How many documents a back-end holds in one locale, and when it last indexed one.
 */
final readonly class SearchIndexStatus
{
    /** @var non-empty-string */
    public string $locale;

    public int $documents;

    public ?DateTimeImmutable $lastIndexedAt;

    /**
     * @param string                 $locale        BCP 47 tag
     * @param int                    $documents     number of documents in that locale
     * @param DateTimeImmutable|null $lastIndexedAt when a document was last added or replaced; stored in UTC
     */
    public function __construct(string $locale, int $documents, ?DateTimeImmutable $lastIndexedAt = null)
    {
        $this->locale = Guard::locale($locale, 'Search index status locale');
        $this->documents = Guard::notNegative($documents, sprintf('Number of documents in "%s"', $locale));
        $this->lastIndexedAt = $lastIndexedAt === null ? null : Guard::utc($lastIndexedAt);
    }
}

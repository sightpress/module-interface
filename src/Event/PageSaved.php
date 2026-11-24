<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A page's text in one language was saved as a new revision. Part of the stable event list (1.0 API).
 */
final readonly class PageSaved
{
    /** @var non-empty-string */
    public string $locale;

    /**
     * @param int      $pageId   the page
     * @param string   $locale   the language that was saved (BCP 47)
     * @param int      $revision the new revision number
     * @param int|null $savedBy  the person who saved it; null for the command line
     */
    public function __construct(
        public int $pageId,
        string $locale,
        public int $revision,
        public ?int $savedBy,
    ) {
        Guard::notNegative($pageId, 'Page id');
        $this->locale = Guard::locale($locale, 'Page locale');
        Guard::notNegative($revision, 'Revision');
    }
}

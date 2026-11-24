<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A page was started, in its first language. Part of the stable event list (1.0 API).
 */
final readonly class PageCreated
{
    /** @var non-empty-string */
    public string $locale;

    /**
     * @param int      $pageId   the new page
     * @param string   $locale   its first language (BCP 47, for example `en-GB`)
     * @param int|null $authorId the person who started it; null for the command line or starter content
     */
    public function __construct(
        public int $pageId,
        string $locale,
        public ?int $authorId,
    ) {
        Guard::notNegative($pageId, 'Page id');
        $this->locale = Guard::locale($locale, 'Page locale');
    }
}

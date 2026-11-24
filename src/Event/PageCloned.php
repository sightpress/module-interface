<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A page was copied as a new draft. Part of the stable event list (1.0 API).
 */
final readonly class PageCloned
{
    /**
     * @param int      $pageId   the new copy
     * @param int      $sourceId the page it was copied from
     * @param int|null $clonedBy the person who copied it; null for the command line
     */
    public function __construct(
        public int $pageId,
        public int $sourceId,
        public ?int $clonedBy,
    ) {
        Guard::notNegative($pageId, 'Page id');
        Guard::notNegative($sourceId, 'Source page id');
    }
}

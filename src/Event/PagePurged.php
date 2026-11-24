<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * Pages were deleted for good (emptied from the trash). Modules that keep data about pages forget them here.
 * Part of the stable event list (1.0 API).
 */
final readonly class PagePurged
{
    /** @var list<int> */
    public array $pageIds;

    /**
     * @param int       $pageId   the page that was deleted
     * @param string    $title    its title, as it can no longer be looked up
     * @param list<int> $pageIds  every page that was deleted: the page and the pages under it
     * @param int|null  $purgedBy the person who deleted it; null for the command line
     */
    public function __construct(
        public int $pageId,
        public string $title,
        array $pageIds,
        public ?int $purgedBy,
    ) {
        Guard::notNegative($pageId, 'Page id');
        $this->pageIds = Guard::listOfIds($pageIds, 'Deleted page ids');
    }
}

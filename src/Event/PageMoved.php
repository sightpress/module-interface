<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A page was moved to another place in the page tree (its address may have changed with it). Part of the stable
 * event list (1.0 API).
 */
final readonly class PageMoved
{
    /**
     * @param int      $pageId       the page that moved (the pages under it moved with it)
     * @param int|null $fromParentId its parent before; null at the top level
     * @param int|null $toParentId   its parent now; null at the top level
     * @param int      $position     its place among its new siblings, from 0
     * @param int      $revision     the page's revision after the move
     * @param int|null $movedBy      the person who moved it; null for the command line
     */
    public function __construct(
        public int $pageId,
        public ?int $fromParentId,
        public ?int $toParentId,
        public int $position,
        public int $revision,
        public ?int $movedBy,
    ) {
        Guard::notNegative($pageId, 'Page id');
        Guard::notNegative($position, 'Page position');
        Guard::notNegative($revision, 'Revision');
    }
}

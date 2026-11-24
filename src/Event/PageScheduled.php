<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use DateTimeImmutable;
use SightPress\Module\Internal\Guard;

/**
 * A page's publishing schedule was set, changed or cleared. Part of the stable event list (1.0 API).
 */
final readonly class PageScheduled
{
    public ?DateTimeImmutable $publishAt;

    public ?DateTimeImmutable $unpublishAt;

    /**
     * @param int                    $pageId      the page
     * @param DateTimeImmutable|null $publishAt   when `schedule:run` publishes it (stored in UTC); null for none
     * @param DateTimeImmutable|null $unpublishAt when it takes it off the site (UTC); null for none
     * @param int|null               $actorId     the person who scheduled it; null for the command line
     */
    public function __construct(
        public int $pageId,
        ?DateTimeImmutable $publishAt,
        ?DateTimeImmutable $unpublishAt,
        public ?int $actorId,
    ) {
        Guard::notNegative($pageId, 'Page id');
        $this->publishAt = $publishAt === null ? null : Guard::utc($publishAt);
        $this->unpublishAt = $unpublishAt === null ? null : Guard::utc($unpublishAt);
    }
}

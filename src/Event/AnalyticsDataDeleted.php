<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

/**
 * The site's visit statistics were deleted. Part of the stable event list (1.0 API).
 */
final readonly class AnalyticsDataDeleted
{
    /**
     * @param int|null $actorId the person who deleted them; null for the command line
     */
    public function __construct(public ?int $actorId)
    {
    }
}

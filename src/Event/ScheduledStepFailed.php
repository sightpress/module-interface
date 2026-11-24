<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * `schedule:run` couldn't publish or unpublish a page at its scheduled time. Part of the stable event list (1.0 API).
 */
final readonly class ScheduledStepFailed
{
    /**
     * @param int      $pageId      the page
     * @param string   $transition  `publish` or `unpublish`
     * @param string   $reason      why, in plain language
     * @param int|null $scheduledBy the person who set the schedule
     */
    public function __construct(
        public int $pageId,
        public string $transition,
        public string $reason,
        public ?int $scheduledBy,
    ) {
        Guard::notNegative($pageId, 'Page id');
        if ($transition !== 'publish' && $transition !== 'unpublish') {
            throw new InvalidValueException(sprintf('Scheduled step is not valid ("%s"): use publish or unpublish.', $transition));
        }
    }
}

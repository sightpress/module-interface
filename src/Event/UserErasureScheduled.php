<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use DateTimeImmutable;
use SightPress\Module\Internal\Guard;

/**
 * An account asked to be deleted, or an admin deleted it: it is deactivated now and erased after the grace period,
 * unless the person signs in before then. Carries ids and the date only. Part of the stable event list (1.0 API,
 * since 0.6).
 */
final readonly class UserErasureScheduled
{
    public DateTimeImmutable $erasesAt;

    /**
     * @param int               $userId      the account
     * @param int|null          $requestedBy who asked: the account itself or an admin; null for the command line
     * @param DateTimeImmutable $erasesAt    when it will be erased (stored in UTC)
     */
    public function __construct(
        public int $userId,
        public ?int $requestedBy,
        DateTimeImmutable $erasesAt,
    ) {
        Guard::notNegative($userId, 'User id');
        $this->erasesAt = Guard::utc($erasesAt);
    }
}

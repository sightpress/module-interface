<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A scheduled erasure was cancelled before it happened: the person signed in during the grace period, or an admin
 * cancelled it. Carries ids only. Part of the stable event list (1.0 API, since 0.6).
 */
final readonly class UserErasureCancelled
{
    /**
     * @param int      $userId      the account
     * @param int|null $cancelledBy the account itself or an admin; null for the command line
     */
    public function __construct(
        public int $userId,
        public ?int $cancelledBy,
    ) {
        Guard::notNegative($userId, 'User id');
    }
}

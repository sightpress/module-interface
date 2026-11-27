<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * An account was erased: its data is gone and the id now belongs to the *Former user* tombstone. Every module's
 * personal data provider has already run; listeners stop any work left for the person. Carries ids only. Part of
 * the stable event list (1.0 API, since 0.6).
 */
final readonly class UserErased
{
    /**
     * @param int      $userId      the account, now a tombstone
     * @param int|null $requestedBy who asked: the account itself (after the grace period) or an admin; null for the
     *                              command line
     */
    public function __construct(
        public int $userId,
        public ?int $requestedBy,
    ) {
        Guard::notNegative($userId, 'User id');
    }
}

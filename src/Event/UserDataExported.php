<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A copy of an account's data was made (*Download your data*, or `site:user:export`). Carries ids only. Part of the
 * stable event list (1.0 API, since 0.6).
 */
final readonly class UserDataExported
{
    /**
     * @param int      $userId      the account
     * @param int|null $requestedBy who asked: the account itself or an admin; null for the command line
     */
    public function __construct(
        public int $userId,
        public ?int $requestedBy,
    ) {
        Guard::notNegative($userId, 'User id');
    }
}

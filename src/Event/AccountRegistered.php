<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * Someone signed up and confirmed their email address. Part of the stable event list (1.0 API).
 */
final readonly class AccountRegistered
{
    /**
     * @param int         $userId      the new account
     * @param string      $role        the role it got
     * @param string|null $refusedRole the site's sign-up role when it wasn't safe for people who sign up, so `member`
     *                                 was given instead; null otherwise
     */
    public function __construct(
        public int $userId,
        public string $role,
        public ?string $refusedRole = null,
    ) {
        Guard::notNegative($userId, 'User id');
        Guard::notBlank($role, 'Role');
    }
}

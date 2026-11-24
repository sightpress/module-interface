<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * Someone's role on the site changed. Part of the stable event list (1.0 API).
 */
final readonly class RoleChanged
{
    /**
     * @param int         $userId    the account
     * @param int|null    $changedBy the admin who changed it; null for the command line
     * @param string|null $from      the role before; null when it had none
     * @param string      $to        the role now
     */
    public function __construct(
        public int $userId,
        public ?int $changedBy,
        public ?string $from,
        public string $to,
    ) {
        Guard::notNegative($userId, 'User id');
        Guard::notBlank($to, 'Role');
    }
}

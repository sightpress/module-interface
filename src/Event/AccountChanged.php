<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * Details of an account changed (by the person or by an admin). Never carries the old or new values, so no personal
 * data travels with it. Part of the stable event list (1.0 API).
 */
final readonly class AccountChanged
{
    /** @var list<string> */
    public array $fields;

    /**
     * @param int          $userId    the account
     * @param int|null     $changedBy the person who changed it (the account itself, or an admin); null for the
     *                                command line
     * @param list<string> $fields    what changed: `first_name`, `last_name`, `display_name`, `avatar`, `timezone`,
     *                                `email`, `password`, `status` …
     */
    public function __construct(
        public int $userId,
        public ?int $changedBy,
        array $fields,
    ) {
        Guard::notNegative($userId, 'User id');
        $this->fields = Guard::listOfStrings($fields, 'Changed account fields');
    }
}

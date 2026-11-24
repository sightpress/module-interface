<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use DateTimeImmutable;
use SightPress\Module\Internal\Guard;

/**
 * Someone replied STOP (or START) to the site's messages. After STOP, core sends that number no more notifications.
 */
final readonly class SmsOptChange
{
    public DateTimeImmutable $at;

    /**
     * @param PhoneNumber       $from    the number that replied
     * @param bool              $stopped true for STOP (and its other words), false for START
     * @param DateTimeImmutable $at      when (stored in UTC)
     */
    public function __construct(
        public PhoneNumber $from,
        public bool $stopped,
        DateTimeImmutable $at,
    ) {
        $this->at = Guard::utc($at);
    }
}

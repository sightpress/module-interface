<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

/**
 * Why a text message is sent. Providers may route security codes differently (some have a separate, faster route
 * for one-time codes), and core keeps a small part of the budget for them.
 */
enum SmsPurpose: string
{
    /** A 6-digit code someone types on a page: never contains a link. */
    case SecurityCode = 'security_code';

    /** A notification the person chose to get by text. */
    case Notification = 'notification';
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Notification;

/**
 * How a notification reaches a person. Email always works; SMS only on sites that set up text messages and for
 * people who chose it.
 */
enum NotificationChannel: string
{
    case Email = 'email';

    case Sms = 'sms';
}

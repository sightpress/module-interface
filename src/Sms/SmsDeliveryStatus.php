<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

enum SmsDeliveryStatus: string
{
    case Delivered = 'delivered';

    /** Not delivered, and the provider has given up. */
    case Failed = 'failed';
}

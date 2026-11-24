<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

/**
 * What kind of line a number is, from a provider's lookup. Only mobile numbers can receive codes reliably; premium
 * rate numbers are always refused, as they are used to run up costs.
 */
enum PhoneLineType: string
{
    case Mobile = 'mobile';

    case Landline = 'landline';

    case Voip = 'voip';

    case TollFree = 'toll_free';

    case PremiumRate = 'premium_rate';

    case Unknown = 'unknown';
}

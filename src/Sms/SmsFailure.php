<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

/**
 * Why a provider call failed, so core can tell people in plain language what to do.
 */
enum SmsFailure: string
{
    /** The key or account is wrong or was revoked. */
    case InvalidKey = 'invalid_key';

    /** The provider refused the number (not a real number, or not one it can send to). */
    case InvalidNumber = 'invalid_number';

    /** The provider doesn't deliver to that country, or the account isn't allowed to. */
    case UnsupportedCountry = 'unsupported_country';

    /** The number replied STOP or the carrier blocks it; it gets nothing until it replies START. */
    case OptedOut = 'opted_out';

    /** The account has no credit left or reached its spending limit. */
    case NoCredit = 'no_credit';

    case RateLimited = 'rate_limited';

    case Timeout = 'timeout';

    /** The provider can't do this (for example a lookup by a provider without one). */
    case Unsupported = 'unsupported';

    /** A webhook request didn't carry a valid signature, or was replayed. */
    case InvalidSignature = 'invalid_signature';

    /** Anything else: the provider is down or answered with something unexpected. */
    case Unavailable = 'unavailable';
}

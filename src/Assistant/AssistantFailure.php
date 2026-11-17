<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

/**
 * Why an assistant call failed. Core turns each case into a plain-language message in the person's language,
 * with what they can do about it.
 */
enum AssistantFailure: string
{
    /** The provider refused the API key (wrong, revoked or without access). */
    case InvalidKey = 'invalid_key';

    /** The provider account has no credit or reached its spending limit. */
    case QuotaExceeded = 'quota_exceeded';

    /** The provider asked to slow down (too many requests); trying later may work. */
    case RateLimited = 'rate_limited';

    /** The provider didn't answer in time. */
    case Timeout = 'timeout';

    /** The model doesn't exist (any more) or the key can't use it. */
    case ModelUnavailable = 'model_unavailable';

    /** The provider or model refused to answer this request. */
    case Refused = 'refused';

    /** The answer couldn't be used (the task's output check failed); trying again may work. */
    case InvalidOutput = 'invalid_output';

    /** Anything else: the service is down, the network failed, the reply couldn't be read. */
    case Unavailable = 'unavailable';
}

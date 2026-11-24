<?php

declare(strict_types=1);

namespace SightPress\Module\Exception;

use RuntimeException;
use SightPress\Module\Sms\SmsFailure;
use Throwable;

/**
 * A text message provider call failed. The message must not contain the phone number, the text or the key.
 */
final class SmsException extends RuntimeException
{
    public function __construct(public readonly SmsFailure $failure, string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message === '' ? sprintf('Text message provider call failed: %s.', $failure->value) : $message, 0, $previous);
    }
}

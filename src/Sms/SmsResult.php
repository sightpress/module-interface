<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use SightPress\Module\Internal\Guard;

/**
 * The provider accepted a message for delivery. Failures are thrown as {@see \SightPress\Module\Exception\SmsException}.
 */
final readonly class SmsResult
{
    /** @var non-empty-string */
    public string $messageId;

    public int $parts;

    /**
     * @param string $messageId the provider's id for the message, which delivery receipts name
     * @param int    $parts     how many parts the provider charged for
     */
    public function __construct(string $messageId, int $parts = 1)
    {
        $this->messageId = Guard::notBlank($messageId, 'Text message id');
        $this->parts = max(1, Guard::notNegative($parts, 'Text message parts'));
    }
}

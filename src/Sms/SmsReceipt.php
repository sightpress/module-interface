<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use DateTimeImmutable;
use SightPress\Module\Internal\Guard;

/**
 * A provider's report that a message was delivered, or that it failed.
 */
final readonly class SmsReceipt
{
    /** @var non-empty-string */
    public string $messageId;

    public DateTimeImmutable $at;

    /**
     * @param string            $messageId the id from {@see SmsResult::$messageId}
     * @param SmsDeliveryStatus $status    what happened
     * @param DateTimeImmutable $at        when, as the provider says (stored in UTC)
     * @param string|null       $reason    the provider's reason for a failure, without the number
     */
    public function __construct(
        string $messageId,
        public SmsDeliveryStatus $status,
        DateTimeImmutable $at,
        public ?string $reason = null,
    ) {
        $this->messageId = Guard::notBlank($messageId, 'Text message id');
        $this->at = Guard::utc($at);
    }
}

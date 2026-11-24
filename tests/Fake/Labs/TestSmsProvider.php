<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Labs;

use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;
use SightPress\Module\Exception\SmsException;
use SightPress\Module\Sms\PhoneLineType;
use SightPress\Module\Sms\PhoneLookupResult;
use SightPress\Module\Sms\PhoneNumber;
use SightPress\Module\Sms\SmsCapabilities;
use SightPress\Module\Sms\SmsConnection;
use SightPress\Module\Sms\SmsDeliveryStatus;
use SightPress\Module\Sms\SmsFailure;
use SightPress\Module\Sms\SmsMessage;
use SightPress\Module\Sms\SmsOptChange;
use SightPress\Module\Sms\SmsProvider;
use SightPress\Module\Sms\SmsReceipt;
use SightPress\Module\Sms\SmsResult;
use SightPress\Module\Sms\SmsWebhooks;

/**
 * A text message provider written only against this package, as an adapter module would: keeps what it "sends",
 * treats UK numbers in the Ofcom drama range (+447700900…) as mobiles and checks a shared-secret webhook header.
 */
final class TestSmsProvider implements SmsProvider, SmsWebhooks
{
    /** @var list<SmsMessage> */
    public array $sent = [];

    public function id(): string
    {
        return 'test';
    }

    public function label(): string
    {
        return 'Test provider';
    }

    public function description(): string
    {
        return 'Sends nothing: messages stay in memory. For tests only.';
    }

    public function capabilities(): SmsCapabilities
    {
        return new SmsCapabilities(lookup: true, deliveryReceipts: true, optOutReplies: true, alphanumericSender: true, countries: ['GB', 'PT']);
    }

    public function send(SmsMessage $message, SmsConnection $connection): SmsResult
    {
        if ($connection->key !== 'test-key-123456') {
            throw new SmsException(SmsFailure::InvalidKey);
        }
        $this->sent[] = $message;

        return new SmsResult('msg-' . count($this->sent), $message->parts());
    }

    public function lookup(PhoneNumber $number, SmsConnection $connection): PhoneLookupResult
    {
        if (str_starts_with($number->e164, '+447700900')) {
            return new PhoneLookupResult(true, PhoneLineType::Mobile, 'gb');
        }
        if (str_starts_with($number->e164, '+449')) {
            return new PhoneLookupResult(true, PhoneLineType::PremiumRate, 'GB');
        }

        return new PhoneLookupResult(false);
    }

    public function readWebhook(ServerRequestInterface $request, SmsConnection $connection): array
    {
        if ($request->getHeaderLine('X-Test-Signature') !== hash_hmac('sha256', (string) $request->getBody(), $connection->key)) {
            throw new SmsException(SmsFailure::InvalidSignature);
        }
        /** @var array{receipts?: list<array{id: string, ok: bool}>, stop?: list<string>} $body */
        $body = json_decode((string) $request->getBody(), true, flags: JSON_THROW_ON_ERROR);
        $at = new DateTimeImmutable('2026-11-24T12:00:00+00:00');
        $reports = [];
        foreach ($body['receipts'] ?? [] as $receipt) {
            $reports[] = new SmsReceipt($receipt['id'], $receipt['ok'] ? SmsDeliveryStatus::Delivered : SmsDeliveryStatus::Failed, $at, $receipt['ok'] ? null : 'Handset unreachable');
        }
        foreach ($body['stop'] ?? [] as $number) {
            $reports[] = new SmsOptChange(new PhoneNumber($number), true, $at);
        }

        return $reports;
    }
}

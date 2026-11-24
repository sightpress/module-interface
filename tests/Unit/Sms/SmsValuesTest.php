<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Sms;

use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Exception\SmsException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Sms\PhoneLineType;
use SightPress\Module\Sms\PhoneLookupResult;
use SightPress\Module\Sms\PhoneNumber;
use SightPress\Module\Sms\SmsCapabilities;
use SightPress\Module\Sms\SmsConnection;
use SightPress\Module\Sms\SmsDeliveryStatus;
use SightPress\Module\Sms\SmsFailure;
use SightPress\Module\Sms\SmsOptChange;
use SightPress\Module\Sms\SmsReceipt;
use SightPress\Module\Sms\SmsResult;

#[CoversClass(PhoneNumber::class)]
#[CoversClass(PhoneLookupResult::class)]
#[CoversClass(SmsCapabilities::class)]
#[CoversClass(SmsConnection::class)]
#[CoversClass(SmsResult::class)]
#[CoversClass(SmsReceipt::class)]
#[CoversClass(SmsOptChange::class)]
#[CoversClass(SmsException::class)]
#[UsesClass(Guard::class)]
final class SmsValuesTest extends TestCase
{
    private const string CANARY = '+447700900123';

    public function testPhoneNumberIsMaskedEverywhereButItsValue(): void
    {
        $number = new PhoneNumber(self::CANARY);

        self::assertSame(self::CANARY, $number->e164);
        self::assertSame('+44•••••••123', $number->masked());
        self::assertTrue($number->equals(new PhoneNumber(self::CANARY)));
        self::assertFalse($number->equals(new PhoneNumber('+351912345678')));
        self::assertStringNotContainsString('7700900123', print_r($number, true));
        self::assertStringNotContainsString('7700900123', var_export($number->__debugInfo(), true));
        self::assertSame('+12••567', new PhoneNumber('+1234567')->masked());

        $this->expectException(LogicException::class);
        serialize($number);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function badNumbers(): iterable
    {
        yield 'national' => ['07700 900123'];
        yield 'no plus' => ['447700900456'];
        yield 'leading zero' => ['+0447700900123'];
        yield 'too short' => ['+123456'];
        yield 'too long' => ['+1234567890123456'];
        yield 'letters' => ['+44770090012a'];
    }

    #[DataProvider('badNumbers')]
    public function testBadNumbersAreRefusedWithoutEchoingThem(string $value): void
    {
        try {
            new PhoneNumber($value);
            self::fail('Accepted ' . $value);
        } catch (InvalidValueException $e) {
            self::assertStringStartsWith('Phone number is not valid: use E.164', $e->getMessage());
            self::assertStringNotContainsString($value, $e->getMessage());
        }
    }

    public function testLookupResult(): void
    {
        $mobile = new PhoneLookupResult(true, PhoneLineType::Mobile, 'gb');
        self::assertTrue($mobile->isMobile());
        self::assertSame('GB', $mobile->country);

        $landline = new PhoneLookupResult(true, PhoneLineType::Landline);
        self::assertFalse($landline->isMobile());
        self::assertNull($landline->country);

        $unknown = new PhoneLookupResult(false, PhoneLineType::Mobile);
        self::assertFalse($unknown->isMobile());
        self::assertSame(PhoneLineType::Unknown, new PhoneLookupResult(false)->lineType);

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Country is not valid ("GBR")');
        new PhoneLookupResult(true, PhoneLineType::Mobile, 'GBR');
    }

    public function testCapabilities(): void
    {
        $defaults = new SmsCapabilities();
        self::assertFalse($defaults->lookup || $defaults->deliveryReceipts || $defaults->optOutReplies || $defaults->alphanumericSender);
        self::assertTrue($defaults->delivers('JP'));

        $uk = new SmsCapabilities(true, true, true, true, ['gb', 'PT']);
        self::assertSame(['GB', 'PT'], $uk->countries);
        self::assertTrue($uk->delivers('gb'));
        self::assertFalse($uk->delivers('US'));

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Provider countries has duplicates: GB.');
        new SmsCapabilities(countries: ['GB', 'gb']);
    }

    public function testConnectionHidesItsKey(): void
    {
        $connection = new SmsConnection('sk-test-secret-9876', 'AC123', 'eu-west-2', 'SightPress');

        self::assertSame('sk-test-secret-9876', $connection->key);
        self::assertSame(['AC123', 'eu-west-2', 'SightPress'], [$connection->account, $connection->region, $connection->sender]);
        self::assertSame('••••9876', $connection->maskedKey());
        self::assertSame('••••', new SmsConnection('short')->maskedKey());
        self::assertStringNotContainsString('secret', print_r($connection, true));
        self::assertSame('+447700900123', new SmsConnection('k', sender: '+447700900123')->sender);
        $bare = new SmsConnection('k');
        self::assertNull($bare->account ?? $bare->region ?? $bare->sender);

        $this->expectException(LogicException::class);
        serialize($connection);
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function badConnections(): iterable
    {
        yield 'blank key' => [['key' => ' '], 'The text message provider key must not be empty.'];
        yield 'blank account' => [['account' => ''], 'Text message provider account must not be empty.'];
        yield 'region' => [['region' => 'EU West'], 'Text message provider region is not valid'];
        yield 'sender too long' => [['sender' => 'SightPressOrg'], 'Text message sender is not valid'];
        yield 'sender digits only' => [['sender' => '12345'], 'Text message sender is not valid'];
        yield 'sender symbols' => [['sender' => 'Sight-Press'], 'Text message sender is not valid'];
    }

    /**
     * @param array<string, string> $fields
     */
    #[DataProvider('badConnections')]
    public function testBadConnectionsAreRefused(array $fields, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        new SmsConnection(...['key' => 'k', ...$fields]);
    }

    public function testResultReceiptAndOptChange(): void
    {
        $result = new SmsResult('SM123', 2);
        self::assertSame(['SM123', 2], [$result->messageId, $result->parts]);
        self::assertSame(1, new SmsResult('SM124', 0)->parts);
        self::assertSame(1, new SmsResult('SM125')->parts);

        $receipt = new SmsReceipt('SM123', SmsDeliveryStatus::Failed, new DateTimeImmutable('2026-11-24 12:00+01:00'), 'Handset unreachable');
        self::assertSame(['SM123', SmsDeliveryStatus::Failed, 'Handset unreachable'], [$receipt->messageId, $receipt->status, $receipt->reason]);
        self::assertSame('2026-11-24T11:00:00+00:00', $receipt->at->format(DATE_ATOM));
        self::assertNull(new SmsReceipt('SM1', SmsDeliveryStatus::Delivered, new DateTimeImmutable())->reason);

        $stop = new SmsOptChange(new PhoneNumber(self::CANARY), true, new DateTimeImmutable('2026-11-24T12:00:00+02:00'));
        self::assertTrue($stop->stopped);
        self::assertSame(self::CANARY, $stop->from->e164);
        self::assertSame('10:00', $stop->at->format('H:i'));

        $this->expectException(InvalidValueException::class);
        new SmsResult('');
    }

    public function testExceptionNamesTheFailure(): void
    {
        $default = new SmsException(SmsFailure::NoCredit);
        self::assertSame(SmsFailure::NoCredit, $default->failure);
        self::assertSame('Text message provider call failed: no_credit.', $default->getMessage());

        $previous = new \RuntimeException('HTTP 401');
        $custom = new SmsException(SmsFailure::InvalidKey, 'The key was revoked.', $previous);
        self::assertSame('The key was revoked.', $custom->getMessage());
        self::assertSame($previous, $custom->getPrevious());
    }
}

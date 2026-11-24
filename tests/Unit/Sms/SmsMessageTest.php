<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Sms;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Sms\PhoneNumber;
use SightPress\Module\Sms\SmsEncoding;
use SightPress\Module\Sms\SmsMessage;
use SightPress\Module\Sms\SmsPurpose;

#[CoversClass(SmsMessage::class)]
#[UsesClass(PhoneNumber::class)]
#[UsesClass(Guard::class)]
final class SmsMessageTest extends TestCase
{
    public function testMessage(): void
    {
        $message = new SmsMessage(self::to(), 'Your Demo code is 123456. It expires in 10 minutes.', SmsPurpose::SecurityCode, 'code:42');

        self::assertSame('+447700900123', $message->to->e164);
        self::assertSame(SmsPurpose::SecurityCode, $message->purpose);
        self::assertSame('code:42', $message->reference);
        self::assertSame(SmsEncoding::Gsm7, $message->encoding());
        self::assertSame(1, $message->parts());
        self::assertSame(['to' => '+44•••••••123', 'purpose' => 'security_code', 'parts' => 1, 'reference' => 'code:42'], $message->__debugInfo());

        $notification = new SmsMessage(self::to(), 'A page is waiting for your review.');
        self::assertSame(SmsPurpose::Notification, $notification->purpose);
        self::assertNull($notification->reference);
    }

    /**
     * @return iterable<string, array{string, SmsEncoding, int, int}>
     */
    public static function lengths(): iterable
    {
        yield '160 GSM fits one part' => [str_repeat('a', 160), SmsEncoding::Gsm7, 160, 1];
        yield '161 GSM needs two' => [str_repeat('a', 161), SmsEncoding::Gsm7, 161, 2];
        yield 'GSM accents' => ['Olá, João: é às 20h', SmsEncoding::Ucs2, 19, 1];
        yield 'GSM-only accents' => ['Café für Åse', SmsEncoding::Gsm7, 12, 1];
        yield 'extension characters count twice' => ['Price: 5€ [incl. VAT]', SmsEncoding::Gsm7, 24, 1];
        yield 'emoji counts twice in UCS-2' => ['Hi 👋', SmsEncoding::Ucs2, 5, 1];
        yield '70 UCS-2 fits one part' => [str_repeat('ש', 70), SmsEncoding::Ucs2, 70, 1];
        yield '71 UCS-2 needs two' => [str_repeat('ש', 71), SmsEncoding::Ucs2, 71, 2];
        yield 'new lines are GSM' => ["Line 1\nLine 2\r\n", SmsEncoding::Gsm7, 15, 1];
    }

    #[DataProvider('lengths')]
    public function testEncodingLengthAndParts(string $text, SmsEncoding $encoding, int $length, int $parts): void
    {
        $message = new SmsMessage(self::to(), $text);

        self::assertSame($encoding, $message->encoding());
        self::assertSame($length, $message->length());
        self::assertSame($parts, $message->parts());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function links(): iterable
    {
        yield 'https' => ['Your code is 123456: https://example.org/verify'];
        yield 'www' => ['Code 123456 or visit www.example.org'];
        yield 'bare host and path' => ['Code 123456, see example.org/verify'];
    }

    #[DataProvider('links')]
    public function testSecurityCodeMessagesHaveNoLink(string $text): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('A security code message must not contain a link');

        new SmsMessage(self::to(), $text, SmsPurpose::SecurityCode);
    }

    public function testNotificationsMayHaveALink(): void
    {
        self::assertSame(1, new SmsMessage(self::to(), 'New review request: https://example.org/admin')->parts());
    }

    public function testTooLongIsRefused(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The text message is too long: it needs 7 parts, at most 6 are allowed.');

        new SmsMessage(self::to(), str_repeat('a', 153 * 6 + 1));
    }

    public function testBlankAndBadReferenceAreRefused(): void
    {
        try {
            new SmsMessage(self::to(), ' ');
            self::fail('Blank text accepted.');
        } catch (InvalidValueException $e) {
            self::assertSame('Text message must not be empty.', $e->getMessage());
        }

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Text message reference is not valid');
        new SmsMessage(self::to(), 'Hi', reference: 'has space');
    }

    private static function to(): PhoneNumber
    {
        return new PhoneNumber('+447700900123');
    }
}

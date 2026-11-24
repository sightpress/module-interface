<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One text message to send. Holds no page content and no secret other than a security code; a security code
 * message never holds a link (verification is always a code typed on a page).
 */
final readonly class SmsMessage
{
    /** Longer messages are refused: they cost more parts than any notification needs. */
    public const int MAX_PARTS = 6;

    /** The GSM 03.38 basic alphabet (one character each). */
    private const string GSM_BASIC = "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";

    /** The GSM 03.38 extension table (two characters each). */
    private const string GSM_EXTENDED = "^{}\\[~]|€\f";

    /** @var non-empty-string */
    public string $text;

    public ?string $reference;

    /**
     * @param PhoneNumber $to        who gets it
     * @param string      $text      the message
     * @param SmsPurpose  $purpose   why it is sent
     * @param string|null $reference core's id for it (letters, digits, `.`, `_`, `:` and `-`, up to 64), which
     *                               providers send back in delivery receipts when they can
     */
    public function __construct(
        public PhoneNumber $to,
        string $text,
        public SmsPurpose $purpose = SmsPurpose::Notification,
        ?string $reference = null,
    ) {
        $this->text = Guard::notBlank($text, 'Text message');
        if ($purpose === SmsPurpose::SecurityCode && preg_match('#https?://|www\.|\b[a-z0-9-]+\.[a-z]{2,}/#i', $text) === 1) {
            throw new InvalidValueException('A security code message must not contain a link: people type the code on the page they are on.');
        }
        $this->reference = $reference === null ? null : Guard::matches($reference, '/^[A-Za-z0-9._:-]{1,64}$/', 'Text message reference', 'use up to 64 letters, digits, ".", "_", ":" or "-"');
        if ($this->parts() > self::MAX_PARTS) {
            throw new InvalidValueException(sprintf('The text message is too long: it needs %d parts, at most %d are allowed.', $this->parts(), self::MAX_PARTS));
        }
    }

    public function encoding(): SmsEncoding
    {
        foreach (mb_str_split($this->text) as $character) {
            if (!str_contains(self::GSM_BASIC, $character) && !str_contains(self::GSM_EXTENDED, $character)) {
                return SmsEncoding::Ucs2;
            }
        }

        return SmsEncoding::Gsm7;
    }

    /**
     * Its length in the units of its encoding: GSM characters (extension characters count twice) or UTF-16 code
     * units (an emoji counts twice).
     */
    public function length(): int
    {
        if ($this->encoding() === SmsEncoding::Ucs2) {
            return intdiv(strlen((string) mb_convert_encoding($this->text, 'UTF-16BE', 'UTF-8')), 2);
        }
        $length = 0;
        foreach (mb_str_split($this->text) as $character) {
            $length += str_contains(self::GSM_EXTENDED, $character) ? 2 : 1;
        }

        return $length;
    }

    /**
     * How many message parts it is sent as (providers charge per part).
     */
    public function parts(): int
    {
        [$single, $multi] = $this->encoding() === SmsEncoding::Gsm7 ? [160, 153] : [70, 67];
        $length = $this->length();

        return $length <= $single ? 1 : (int) ceil($length / $multi);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return ['to' => $this->to->masked(), 'purpose' => $this->purpose->value, 'parts' => $this->parts(), 'reference' => $this->reference];
    }
}

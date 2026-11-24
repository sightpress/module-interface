<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

/**
 * How a text message is encoded, which sets how many characters fit in one message part.
 */
enum SmsEncoding: string
{
    /** The GSM 03.38 alphabet: 160 characters in one part, 153 per part when split. */
    case Gsm7 = 'gsm7';

    /** Any other text (accents outside GSM, emoji, non-Latin scripts): 70 in one part, 67 per part when split. */
    case Ucs2 = 'ucs2';
}

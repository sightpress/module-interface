<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use LogicException;
use SensitiveParameter;
use SightPress\Module\Exception\InvalidValueException;

/**
 * A phone number in E.164 form (`+447700900123`). Personal data: show it with {@see masked()} everywhere except to
 * the person and to admins who manage users, and never write it in full to logs, exceptions or the audit log.
 *
 * Parsing what people type (`07700 900123` in the UK) is core's job; this only holds the result.
 */
final readonly class PhoneNumber
{
    /** @var non-empty-string */
    public string $e164;

    public function __construct(#[SensitiveParameter] string $e164)
    {
        if (preg_match('/^\+[1-9]\d{6,14}$/', $e164) !== 1) {
            // The number itself stays out of the message, as messages end up in logs.
            throw new InvalidValueException('Phone number is not valid: use E.164, a "+" then 7 to 15 digits, for example "+447700900123".');
        }
        $this->e164 = $e164;
    }

    /**
     * The number with all but the first two and last three digits hidden, for example `+44•••••••123`.
     */
    public function masked(): string
    {
        $digits = substr($this->e164, 1);

        return '+' . substr($digits, 0, 2) . str_repeat('•', strlen($digits) - 5) . substr($digits, -3);
    }

    public function equals(self $other): bool
    {
        return $this->e164 === $other->e164;
    }

    /**
     * @return array{e164: string}
     */
    public function __debugInfo(): array
    {
        return ['e164' => $this->masked()];
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LogicException('A phone number is personal data and must not be serialised; store its e164 value.');
    }
}

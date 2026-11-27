<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use LogicException;
use SensitiveParameter;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Sms\PhoneNumber;

/**
 * The person whose data is exported or erased, on the current site: the account id, and the email address and
 * mobile number for data kept by address (invitations, hashes of where a code was sent).
 *
 * Personal data: it hides the address and number in dumps and must not be serialised or logged.
 */
final readonly class UserRef
{
    /** @var non-empty-string */
    public string $email;

    /**
     * @param int              $userId the account id on this site
     * @param string           $email  the account's email address, as stored
     * @param PhoneNumber|null $phone  the account's mobile number, if it has one
     */
    public function __construct(
        public int $userId,
        #[SensitiveParameter] string $email,
        public ?PhoneNumber $phone = null,
    ) {
        Guard::notNegative($userId, 'User id');
        if (preg_match('/^[^@\s]+@[^@\s]+$/', $email) !== 1) {
            // The address stays out of the message, as messages end up in logs.
            throw new InvalidValueException('Email address of the user is not valid: it must look like name@example.org.');
        }
        $this->email = $email;
    }

    /**
     * @return array{userId: int, email: string, phone: string|null}
     */
    public function __debugInfo(): array
    {
        return ['userId' => $this->userId, 'email' => '•••', 'phone' => $this->phone?->masked()];
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LogicException('A user reference holds personal data and must not be serialised; keep the user id.');
    }
}

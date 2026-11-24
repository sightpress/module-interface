<?php

declare(strict_types=1);

namespace SightPress\Module\Sms;

use LogicException;
use SensitiveParameter;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * A site's account with a provider. Core stores the key encrypted and never shows it again after it is saved.
 *
 * Providers name their credentials differently: `account` is the account or key id (Twilio account SID, Vonage API
 * key, Sinch service plan id, AWS access key id) and `key` the secret that goes with it.
 */
final readonly class SmsConnection
{
    /** @var non-empty-string */
    public string $key;

    public ?string $account;

    public ?string $region;

    public ?string $sender;

    /**
     * @param string      $key     the secret
     * @param string|null $account the account or key id, when the provider has one
     * @param string|null $region  the provider's region, for example `eu` or `eu-west-2`, when it has regions
     * @param string|null $sender  who messages come from: a number in E.164 form, or a name of 3 to 11 letters,
     *                             digits and spaces with at least one letter (alphanumeric sender id)
     */
    public function __construct(
        #[SensitiveParameter] string $key,
        ?string $account = null,
        ?string $region = null,
        ?string $sender = null,
    ) {
        if (trim($key) === '') {
            throw new InvalidValueException('The text message provider key must not be empty.');
        }
        $this->key = $key;
        $this->account = $account === null ? null : Guard::notBlank($account, 'Text message provider account');
        $this->region = $region === null ? null : Guard::matches($region, '/^[a-z0-9][a-z0-9-]{0,31}$/', 'Text message provider region', 'use the provider\'s region code, for example "eu-west-2"');
        $this->sender = $sender === null ? null : Guard::matches(
            $sender,
            '/^(?:\+[1-9]\d{6,14}|(?=[A-Za-z0-9 ]{3,11}$)[A-Za-z0-9 ]*[A-Za-z][A-Za-z0-9 ]*)$/',
            'Text message sender',
            'use a number such as "+447700900123", or a name of 3 to 11 letters, digits and spaces',
        );
    }

    public function maskedKey(): string
    {
        return '••••' . (mb_strlen($this->key) > 8 ? mb_substr($this->key, -4) : '');
    }

    /**
     * @return array<string, string|null>
     */
    public function __debugInfo(): array
    {
        return ['key' => $this->maskedKey(), 'account' => $this->account, 'region' => $this->region, 'sender' => $this->sender];
    }

    /**
     * @return never
     */
    public function __serialize(): array
    {
        throw new LogicException('A text message connection holds a key and must not be serialised.');
    }
}

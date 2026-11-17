<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use LogicException;
use SensitiveParameter;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * How a provider reaches its service for one site: the site's (or tenant's) API key and, for custom endpoints, a
 * base URL. Core decrypts the key just before the call and passes it here; providers must not store it, log it,
 * or put it in an exception message.
 *
 * The key is hidden from `var_dump()` and `print_r()`, and the object can't be serialised.
 */
final readonly class AssistantConnection
{
    /** @var non-empty-string */
    public string $apiKey;

    /** @var non-empty-string|null */
    public ?string $baseUrl;

    /**
     * @param string      $apiKey  the provider API key
     * @param string|null $baseUrl absolute `https://` URL of the service (`http://` only for local development),
     *                             for providers whose {@see AssistantProvider::needsBaseUrl()} is true; core has
     *                             already checked it against the site's network rules
     */
    public function __construct(#[SensitiveParameter] string $apiKey, ?string $baseUrl = null)
    {
        if (trim($apiKey) === '') {
            throw new InvalidValueException('The assistant API key must not be empty.');
        }
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl === null ? null : Guard::matches(
            $baseUrl,
            '#^https?://[^\s/?\#@]+(?:/\S*)?$#',
            'Assistant base URL',
            'use an absolute URL such as "https://ai.example.org/v1", without a user name or password',
        );
    }

    /**
     * The key as people may see it: only its last four characters, for example `••••1234`.
     *
     * @return non-empty-string
     */
    public function maskedKey(): string
    {
        return '••••' . (mb_strlen($this->apiKey) > 8 ? mb_substr($this->apiKey, -4) : '');
    }

    /**
     * @return array{apiKey: non-empty-string, baseUrl: non-empty-string|null}
     */
    public function __debugInfo(): array
    {
        return ['apiKey' => $this->maskedKey(), 'baseUrl' => $this->baseUrl];
    }

    /**
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        throw new LogicException('An assistant connection holds an API key and must not be serialised.');
    }
}

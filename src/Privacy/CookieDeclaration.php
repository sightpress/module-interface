<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Text\Message;

/**
 * A cookie (or browser storage entry) a module sets, for the site's cookie list (since 0.6). Registered with
 * {@see \SightPress\Module\Registrar\PrivacyRegistrar::addCookie()}.
 *
 * One that isn't strictly necessary names the {@see ConsentPurpose} it waits for: the module must check
 * {@see ConsentManager::allows()} before setting it.
 */
final readonly class CookieDeclaration
{
    /** @var non-empty-string */
    public string $name;

    /** @var positive-int|null */
    public ?int $lifetime;

    /** @var non-empty-string|null */
    public ?string $consentPurpose;

    /**
     * @param string      $name              the cookie's or storage key's name, for example `acme_maps_choice`
     * @param Message     $purpose           what it is for, in one plain sentence
     * @param int|null    $lifetime          seconds it lasts; null until the browser is closed
     * @param bool        $strictlyNecessary needed for something the visitor asked for (signing in, a form), so no
     *                                       consent is needed
     * @param string|null $consentPurpose    the purpose it waits for; required unless strictly necessary
     * @param StorageKind $storage           a cookie, or local or session storage
     */
    public function __construct(
        string $name,
        public Message $purpose,
        ?int $lifetime,
        public bool $strictlyNecessary,
        ?string $consentPurpose = null,
        public StorageKind $storage = StorageKind::Cookie,
    ) {
        $this->name = Guard::matches($name, '/^[A-Za-z0-9!#$%&\'*+.^_`|~-]+$/', 'Cookie name', 'use letters, digits and "_", "-" or "."');
        if ($lifetime !== null && $lifetime < 1) {
            throw new InvalidValueException(sprintf('Lifetime of cookie "%s" must be at least 1 second, or null.', $name));
        }
        $this->lifetime = $lifetime;
        if ($strictlyNecessary && $consentPurpose !== null) {
            throw new InvalidValueException(sprintf('Cookie "%s" is strictly necessary, so it can\'t wait for consent.', $name));
        }
        if (!$strictlyNecessary && $consentPurpose === null) {
            throw new InvalidValueException(sprintf('Cookie "%s" is not strictly necessary: name the consent purpose it waits for.', $name));
        }
        $this->consentPurpose = $consentPurpose === null ? null : Guard::matches($consentPurpose, Guard::DOTTED_NAME, 'Consent purpose key', 'use a dotted name such as "acme_maps.embeds"');
    }
}

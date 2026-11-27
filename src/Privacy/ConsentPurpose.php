<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use SightPress\Module\Internal\Guard;
use SightPress\Module\Text\Message;

/**
 * Something a module may only do with consent, such as setting a cookie that isn't strictly necessary or sending
 * news by text message (since 0.6). Registered with
 * {@see \SightPress\Module\Registrar\PrivacyRegistrar::addConsentPurpose()} and asked about through
 * {@see ConsentManager}.
 */
final readonly class ConsentPurpose
{
    /** @var non-empty-string */
    public string $key;

    /**
     * @param string  $key         unique dotted name, starting with the module's prefix, for example
     *                             `acme_maps.embeds`
     * @param Message $label       short name for the choice, for example "Maps from OpenMaps"
     * @param Message $description what saying yes allows, in plain words, next to the choice
     */
    public function __construct(string $key, public Message $label, public Message $description)
    {
        $this->key = Guard::matches($key, Guard::DOTTED_NAME, 'Consent purpose key', 'use a dotted name such as "acme_maps.embeds"');
    }
}

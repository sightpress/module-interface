<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Sms\SmsProvider;

/**
 * Adds a text message (SMS) provider that site admins can choose (since 0.5). Its {@see SmsProvider::id()} must be
 * unique.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface SmsRegistrar
{
    /**
     * @param class-string<SmsProvider> $provider core builds it from the site's container
     */
    public function addProvider(string $provider): void;
}

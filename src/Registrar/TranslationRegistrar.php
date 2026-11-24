<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

/**
 * Adds a module's translations of its interface text (since 0.5).
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface TranslationRegistrar
{
    /**
     * @param string $directory absolute path of a folder of XLIFF 1.2/2.0 files named `<domain>.<locale>.xlf`, for
     *                          example `acme_events.en-GB.xlf`. Use the module's own domain: core's `messages`
     *                          domain is refused. en-GB is the source language and must be there.
     */
    public function add(string $directory): void;
}

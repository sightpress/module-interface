<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Seo\SeoProvider;

/**
 * Adds a module's public pages to the sitemap and `llms.txt` (since 0.5).
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface SeoRegistrar
{
    /**
     * @param class-string<SeoProvider> $provider core builds it from the site's container
     */
    public function addProvider(string $provider): void;
}

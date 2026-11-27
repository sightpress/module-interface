<?php

declare(strict_types=1);

namespace SightPress\Module\Site;

/**
 * The languages the current site is written in (since 0.6), in the site's container. A site with one language shows
 * nothing about languages: modules hide their language switchers, columns, filters and translation actions when
 * {@see isMultilingual()} is false.
 *
 * Implemented by core only; modules use it but must not implement it, so methods can be added in a minor release.
 */
interface SiteLanguages
{
    /**
     * Whether the site has more than one language.
     */
    public function isMultilingual(): bool;

    /**
     * @return non-empty-list<non-empty-string> every language (BCP 47), the default one first
     */
    public function locales(): array;

    /**
     * @return non-empty-string the default language
     */
    public function default(): string;
}

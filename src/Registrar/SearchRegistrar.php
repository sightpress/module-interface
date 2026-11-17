<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Search\SearchBackend;
use SightPress\Module\Search\SearchDocumentProvider;

/**
 * Adds content to site search, or replaces the search back-end.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface SearchRegistrar
{
    /**
     * Adds a provider of search documents. Its {@see SearchDocumentProvider::source()} must be unique.
     *
     * @param class-string<SearchDocumentProvider> $provider core builds it from the site's container
     */
    public function addProvider(string $provider): void;

    /**
     * Makes this back-end hold the site's search index instead of core's built-in one. Only one module may do
     * this; a second one stops the boot with an error that names both modules. After switching back-ends, run
     * `site:search:reindex`.
     *
     * @param class-string<SearchBackend> $backend core builds it from the site's container
     */
    public function useBackend(string $backend): void;
}

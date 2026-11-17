<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

/**
 * Turns a module's content into {@see SearchDocument}s, so site search can find it. Pages are the first provider;
 * modules add theirs with `ModuleRegistrar::search()->addProvider()`.
 *
 * A provider works on the current site only (it is built from the site's container). It returns **published**
 * content only, sets each document's visibility, and leaves out what editors left out of site search. It tells
 * core about changes by dispatching its own events; core's search module (or a module that listens to them) then
 * asks for the changed document with {@see document()}.
 */
interface SearchDocumentProvider
{
    /**
     * Name stored with every document of this provider, unique on the site: usually the module id, for example
     * `page` or `acme.events`.
     *
     * @return non-empty-string
     */
    public function source(): string;

    /**
     * Every document in one locale, for a full reindex. Should yield documents one by one rather than build a
     * list, so large sites don't run out of memory.
     *
     * @param non-empty-string $locale
     *
     * @return iterable<SearchDocument>
     */
    public function documents(string $locale): iterable;

    /**
     * One document, or null when it doesn't exist, isn't published or is left out of search in that locale (core
     * then removes it from the index).
     *
     * @param non-empty-string $ref
     * @param non-empty-string $locale
     */
    public function document(string $ref, string $locale): ?SearchDocument;
}

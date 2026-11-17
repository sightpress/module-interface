<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

/**
 * Stores the search index of one site and answers queries. Core ships a back-end that keeps the index in the
 * site database; a module can replace it with `ModuleRegistrar::search()->useBackend()`, for example to use a
 * search server.
 *
 * A back-end must never return a document the query may not see ({@see SearchQuery::$includeMembersOnly}) or a
 * document in another locale, and must treat the query text as data (never as query syntax of its engine).
 */
interface SearchBackend
{
    /**
     * Adds a document, or replaces the one with the same {@see SearchDocument::key()}.
     */
    public function index(SearchDocument $document): void;

    /**
     * Removes a document in one locale, or in every locale when `$locale` is null. Removing a document that isn't
     * indexed does nothing.
     *
     * @param non-empty-string      $source
     * @param non-empty-string      $ref
     * @param non-empty-string|null $locale
     */
    public function remove(string $source, string $ref, ?string $locale = null): void;

    /**
     * Removes every document in one locale, or the whole index when `$locale` is null (before a full reindex, or
     * when a locale is removed from the site).
     *
     * @param non-empty-string|null $locale
     */
    public function clear(?string $locale = null): void;

    public function query(SearchQuery $query): SearchResults;

    /**
     * What the index holds, one entry per locale that has documents, for `site:search:status`.
     *
     * @return list<SearchIndexStatus>
     */
    public function status(): array;
}

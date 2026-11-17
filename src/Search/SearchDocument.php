<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

use DateTimeImmutable;
use SightPress\Module\Internal\Guard;
use SightPress\Module\ModuleManifest;

/**
 * One searchable thing in one locale, for example one translation of a page, as plain text.
 *
 * Only published content is ever turned into a document. Every field must be plain text: no HTML or Markdown, no
 * private data (email addresses, notes for editors), nothing a visitor of the document's {@see $visibility} could
 * not already read on the page itself. Core escapes every field when it shows results.
 */
final readonly class SearchDocument
{
    /** @var non-empty-string */
    public string $source;

    /** @var non-empty-string */
    public string $ref;

    /** @var non-empty-string */
    public string $locale;

    /** @var non-empty-string */
    public string $path;

    /** @var non-empty-string */
    public string $title;

    /** @var list<non-empty-string> */
    public array $breadcrumb;

    public DateTimeImmutable $updatedAt;

    /**
     * @param string           $source     name of the {@see SearchDocumentProvider} that made it
     *                                     (see {@see SearchDocumentProvider::source()})
     * @param string           $ref        the provider's own id for the thing, for example a page id; the same in
     *                                     every locale
     * @param string           $locale     language of the text, a BCP 47 tag such as `en-GB`
     * @param string           $path       site-relative URL path of the document in that locale, starting with
     *                                     `/`, for example `/pt-pt/sobre`
     * @param string           $title      the document's title, shown as the result's link text
     * @param DateTimeImmutable $updatedAt when its content last changed; stored in UTC
     * @param string           $summary    a short description; may be empty
     * @param string           $body       the rest of the text: block text, alt text, captions, transcripts …
     * @param list<string>     $breadcrumb titles of the ancestors, from the top, shown as text with each result
     * @param SearchVisibility $visibility who may find it
     */
    public function __construct(
        string $source,
        string $ref,
        string $locale,
        string $path,
        string $title,
        DateTimeImmutable $updatedAt,
        public string $summary = '',
        public string $body = '',
        array $breadcrumb = [],
        public SearchVisibility $visibility = SearchVisibility::Public,
    ) {
        $this->source = Guard::matches($source, ModuleManifest::ID_PATTERN, 'Search document source', ModuleManifest::ID_RULE);
        $this->ref = Guard::notBlank($ref, sprintf('Ref of search document from "%s"', $source));
        $field = sprintf('Search document "%s:%s"', $source, $ref);
        $this->locale = Guard::locale($locale, $field . ' locale');
        $this->path = Guard::matches($path, '/^\/(?!\/)\S*$/', $field . ' path', 'use a site-relative path such as "/about"');
        $this->title = Guard::notBlank($title, $field . ' title');

        $trail = [];
        foreach (Guard::listOfStrings($breadcrumb, $field . ' breadcrumb') as $crumb) {
            $trail[] = Guard::notBlank($crumb, $field . ' breadcrumb entry');
        }
        $this->breadcrumb = $trail;
        $this->updatedAt = Guard::utc($updatedAt);
    }

    /**
     * The document's key in an index: source, ref and locale, for example `page:42:pt-PT`.
     *
     * @return non-empty-string
     */
    public function key(): string
    {
        return sprintf('%s:%s:%s', $this->source, $this->ref, $this->locale);
    }
}

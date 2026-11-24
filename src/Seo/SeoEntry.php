<?php

declare(strict_types=1);

namespace SightPress\Module\Seo;

use DateTimeImmutable;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One public page of a module, as search engines and AI readers should see it: listed in the sitemap and in
 * `llms.txt`, and, when the module renders it with {@see \SightPress\Module\Http\PublicScreen}, described in its
 * `<head>` (description, canonical address, other languages, schema.org JSON-LD).
 *
 * Only for pages that are public and may be indexed: core leaves every entry out on members-only sites, on installs
 * that aren't in production and on sites where the module is off.
 */
final readonly class SeoEntry
{
    /** @var non-empty-string */
    public string $path;

    /** @var non-empty-string */
    public string $locale;

    /** @var non-empty-string */
    public string $title;

    public DateTimeImmutable $updatedAt;

    public ?string $description;

    public ?string $markdownPath;

    /** @var array<string, string> */
    public array $alternates;

    /** @var list<array<string, mixed>> */
    public array $structuredData;

    /**
     * @param string                     $path           site-relative address of the page, for example
     *                                                   `/events/open-day`
     * @param string                     $locale         its language (BCP 47)
     * @param string                     $title          its title
     * @param DateTimeImmutable          $updatedAt      when it last changed (stored in UTC)
     * @param string|null                $description    one or two plain sentences for search results
     * @param string|null                $markdownPath   address of its Markdown version, ending in `.md`, when the
     *                                                   module serves one; `llms.txt` links there
     * @param array<string, string>      $alternates     the same page in the site's other languages: locale → path
     * @param list<array<string, mixed>> $structuredData schema.org nodes, each with an `@type`, for example an
     *                                                   `Event`; core adds `@context` and puts them in the page's
     *                                                   JSON-LD graph
     */
    public function __construct(
        string $path,
        string $locale,
        string $title,
        DateTimeImmutable $updatedAt,
        ?string $description = null,
        ?string $markdownPath = null,
        array $alternates = [],
        array $structuredData = [],
    ) {
        $this->path = self::path($path, 'SEO entry path');
        $this->locale = Guard::locale($locale, 'SEO entry locale');
        $this->title = Guard::notBlank($title, 'SEO entry title');
        $this->updatedAt = Guard::utc($updatedAt);
        $this->description = $description === null ? null : Guard::notBlank($description, 'SEO entry description');
        $this->markdownPath = $markdownPath === null ? null : Guard::matches(
            $markdownPath,
            '#^/[^\s?\#]*\.md$#',
            'Markdown path',
            'start with "/" and end with ".md"',
        );

        $checked = [];
        foreach ($alternates as $alternateLocale => $alternatePath) {
            if (!is_string($alternateLocale) || !is_string($alternatePath)) {
                throw new InvalidValueException('Alternates of an SEO entry must map a locale to a path.');
            }
            $checked[Guard::locale($alternateLocale, 'Alternate locale')] = self::path($alternatePath, 'Alternate path');
        }
        $this->alternates = $checked;

        if (!array_is_list($structuredData)) {
            throw new InvalidValueException('Structured data of an SEO entry must be a list of schema.org nodes.');
        }
        foreach ($structuredData as $index => $node) {
            if (!is_array($node) || !isset($node['@type']) || !is_string($node['@type']) || trim($node['@type']) === '') {
                throw new InvalidValueException(sprintf('Structured data node %d of an SEO entry needs an "@type".', $index));
            }
        }
        /** @var list<array<string, mixed>> $structuredData */
        $this->structuredData = $structuredData;
    }

    /**
     * @return non-empty-string
     */
    private static function path(string $path, string $field): string
    {
        return Guard::matches($path, '#^/(?!/)[^\s?\#]*$#', $field, 'start with a single "/" and contain no spaces, query or fragment');
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Http;

use SightPress\Module\Internal\Guard;
use SightPress\Module\Seo\SeoEntry;
use SightPress\Module\Text\Message;

/**
 * A module's public page, rendered by {@see Screens::public()} in the site's theme, with its menus, skip link,
 * language links and footer; the module's template draws the main content, starting with its `<h1>`.
 *
 * With an {@see SeoEntry}, core writes the page's description, canonical address, `hreflang` links, Markdown link
 * and JSON-LD from it, as for core's own pages. Without one, the page gets a title and a canonical address only.
 */
final readonly class PublicScreen
{
    /** @var non-empty-string */
    public string $template;

    /** @var array<string, mixed> */
    public array $context;

    public int $status;

    /**
     * @param string|Message       $title    the page's `<title>` (core adds the site name)
     * @param string               $template a template of the module, for example `@acme_events/event.html.twig`
     * @param array<string, mixed> $context  variables for the template
     * @param int                  $status   HTTP status
     * @param SeoEntry|null        $seo      how search engines see the page; null for pages that aren't listed
     * @param bool                 $noindex  asks search engines not to index the page (search results, forms …)
     */
    public function __construct(
        public string|Message $title,
        string $template,
        array $context = [],
        int $status = 200,
        public ?SeoEntry $seo = null,
        public bool $noindex = false,
    ) {
        if (is_string($title)) {
            Guard::notBlank($title, 'Page title');
        }
        $this->template = Guard::template($template);
        $this->context = Guard::context($context);
        $this->status = Guard::status($status);
    }
}

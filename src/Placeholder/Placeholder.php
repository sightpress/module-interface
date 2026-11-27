<?php

declare(strict_types=1);

namespace SightPress\Module\Placeholder;

use SightPress\Module\Internal\Guard;
use SightPress\Module\Text\Message;

/**
 * A detail editors can put in text, written `{{ acme_events.next_date }}` and replaced by its value when the page is
 * shown (since 0.6). Registered with {@see \SightPress\Module\Registrar\PlaceholderRegistrar::add()} and listed in
 * the editor's *Insert detail* menu.
 *
 * `site.*` and `user.*` belong to core. A personal placeholder (about the person reading) is only filled in on
 * members-only pages, and never in Markdown, `llms.txt`, structured data or the search index.
 */
final readonly class Placeholder
{
    /** @var non-empty-string */
    public string $name;

    /** @var class-string<PlaceholderResolver> */
    public string $resolver;

    /**
     * @param string                            $name        dotted name of at least two parts, starting with the
     *                                                       module's prefix, for example `acme_events.next_date`
     * @param Message                           $label       its name in the menu, for example "Next event date"
     * @param Message                           $description what it shows, in one plain sentence
     * @param PlaceholderType                   $type        the kind of value
     * @param bool                              $personal    whether its value is about the person reading
     * @param class-string<PlaceholderResolver> $resolver    gives its value; core builds it from the site's container
     */
    public function __construct(
        string $name,
        public Message $label,
        public Message $description,
        public PlaceholderType $type,
        public bool $personal,
        string $resolver,
    ) {
        $this->name = Guard::matches(
            $name,
            '/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+$/',
            'Placeholder name',
            'use a dotted name of at least two parts such as "acme_events.next_date"',
        );
        /** @var class-string<PlaceholderResolver> $resolver */
        $resolver = Guard::notBlank($resolver, 'Placeholder resolver');
        $this->resolver = $resolver;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Http;

use SightPress\Module\Internal\Guard;
use SightPress\Module\Text\Message;

/**
 * A module's admin screen, rendered by {@see Screens::admin()} inside the admin panel: core draws the navigation,
 * skip link, notices and the `<h1>` with the title; the module's template draws only what goes under it.
 */
final readonly class AdminScreen
{
    /** @var non-empty-string */
    public string $template;

    /** @var array<string, mixed> */
    public array $context;

    public int $status;

    /**
     * @param string|Message       $title    the screen's `<h1>` and `<title>`
     * @param string               $template a template of the module, for example `@acme_events/admin/list.html.twig`
     * @param array<string, mixed> $context  variables for the template
     * @param int                  $status   HTTP status, for example 422 when a form has errors
     */
    public function __construct(
        public string|Message $title,
        string $template,
        array $context = [],
        int $status = 200,
    ) {
        if (is_string($title)) {
            Guard::notBlank($title, 'Screen title');
        }
        $this->template = Guard::template($template);
        $this->context = Guard::context($context);
        $this->status = Guard::status($status);
    }
}

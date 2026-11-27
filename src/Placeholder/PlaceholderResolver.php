<?php

declare(strict_types=1);

namespace SightPress\Module\Placeholder;

use DateTimeImmutable;

/**
 * Gives the value of a module's placeholders (since 0.6).
 *
 * Implemented by modules; core builds it from the site's container.
 */
interface PlaceholderResolver
{
    /**
     * The value of the placeholder, as plain text, a number or a date; null when there is none (the text then shows
     * the placeholder's fallback). Never HTML: core escapes and formats it for where it goes.
     */
    public function resolve(string $name, PlaceholderContext $context): string|int|float|DateTimeImmutable|null;
}

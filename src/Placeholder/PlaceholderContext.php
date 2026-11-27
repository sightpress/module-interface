<?php

declare(strict_types=1);

namespace SightPress\Module\Placeholder;

/**
 * Where a placeholder is being filled in (since 0.6).
 *
 * Implemented by core only; resolvers use it but must not implement it, so methods can be added in a minor release.
 */
interface PlaceholderContext
{
    /**
     * Locale of the content being shown (BCP 47).
     *
     * @return non-empty-string
     */
    public function locale(): string;

    /**
     * The account reading the page, for personal placeholders on members-only pages only; null everywhere else.
     */
    public function viewerId(): ?int;
}

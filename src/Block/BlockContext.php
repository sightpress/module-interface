<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * What a block type may need to know about where it is rendered.
 *
 * Implemented by core only; block types use it but must not implement it, so methods can be added in a minor
 * release.
 */
interface BlockContext
{
    /**
     * Locale of the content being rendered, as a BCP 47 tag such as `en-GB` or `pt-PT`.
     *
     * @return non-empty-string
     */
    public function locale(): string;

    /**
     * Absolute URL for a path on the current site, for example `/events` → `https://example.org/en/events`.
     * Markdown and structured data must use absolute URLs.
     *
     * @return non-empty-string
     */
    public function absoluteUrl(string $path): string;

    /**
     * Public URL of a media item referenced by id in block data (blocks never store media URLs), or null if the
     * item doesn't exist or isn't public.
     *
     * @param string|null $variant a named size or format, for example `thumbnail`; null for the original
     *
     * @return non-empty-string|null
     */
    public function mediaUrl(string $mediaId, ?string $variant = null): ?string;
}

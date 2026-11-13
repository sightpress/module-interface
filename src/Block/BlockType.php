<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * A kind of content block (heading, image, video, call to action …) that editors can put on a page.
 *
 * Pages store blocks as `{ "type": name(), "version": version(), "data": { … } }`. Core validates the data against
 * {@see schema()} on every save, so the render methods only ever get valid data at the current version.
 *
 * Every method must be free of side effects and must not query the database or the network; anything a block
 * needs at render time comes from the data or the {@see BlockContext}. Media is referenced by id in the data,
 * never by URL.
 */
interface BlockType
{
    /**
     * Unique name stored with every block: lower-case words joined by `_`, with a vendor prefix and a dot for
     * third-party types, for example `image` (core) or `acme.event_card`.
     *
     * @return non-empty-string
     */
    public function name(): string;

    /**
     * Name shown to editors in the block picker, in en-GB, for example "Image". Core translates it with the
     * module's translations.
     *
     * @return non-empty-string
     */
    public function label(): string;

    /**
     * Version of the data format, starting at 1. Raise it whenever {@see schema()} changes in a way old data
     * doesn't fit, and handle the old version in {@see upgradeData()}.
     *
     * @return positive-int
     */
    public function version(): int;

    /**
     * JSON Schema (draft 2020-12) of the block's data, as a PHP array, for example
     * `['type' => 'object', 'required' => ['text'], 'properties' => ['text' => ['type' => 'string']]]`.
     *
     * @return array<string, mixed>
     */
    public function schema(): array;

    /**
     * Turns data stored at an older version into data for the current {@see version()}.
     *
     * @param positive-int         $fromVersion lower than {@see version()}
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function upgradeData(int $fromVersion, array $data): array;

    /**
     * HTML for the public page. Must meet WCAG 2.2 AA, escape every value from the data (rich text is already
     * sanitised by core), use headings, lists, landmarks and alt text correctly, and show nothing private.
     *
     * @param array<string, mixed> $data
     */
    public function render(array $data, BlockContext $context): string;

    /**
     * Markdown version of the block, for readers that prefer text (AI search engines, `/{path}.md`): headings,
     * lists, links with absolute URLs, image alt text, transcripts of audio and video. No theme chrome.
     *
     * @param array<string, mixed> $data
     */
    public function renderMarkdown(array $data, BlockContext $context): string;

    /**
     * Optional schema.org node (JSON-LD, as a PHP array) that core adds to the page's `@graph`, for example a
     * `VideoObject` with its transcript; null when the block adds nothing. Must contain only public data and
     * absolute URLs.
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>|null
     */
    public function structuredData(array $data, BlockContext $context): ?array;

    /**
     * Accessibility checks for this block's data.
     *
     * @return list<A11yRule>
     */
    public function a11yRules(): array;
}

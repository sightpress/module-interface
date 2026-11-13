<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * An accessibility check on one block's data, for example "an image needs alt text or must be marked
 * decorative" or "a video needs a captions track".
 *
 * Core runs every rule of a block type when a block is saved and before a page is published. Checks across blocks
 * (such as heading levels that skip) are core's job, not a block type's.
 */
interface A11yRule
{
    /**
     * Checks data that has already passed the block type's schema. Must have no side effects.
     *
     * @param array<string, mixed> $data
     *
     * @return list<A11yIssue> an empty list when the data is fine
     */
    public function check(array $data): array;
}

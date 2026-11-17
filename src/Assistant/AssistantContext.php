<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

/**
 * What an assistant task may need to know about the request it answers.
 *
 * Implemented by core only; tasks use it but must not implement it, so methods can be added in a minor release.
 */
interface AssistantContext
{
    /**
     * Language the answer must be written in, as a BCP 47 tag: the content's locale for drafting tasks, the
     * person's admin locale for help.
     *
     * @return non-empty-string
     */
    public function locale(): string;

    /**
     * Whether the person holds a permission on this site, so answers only point to what they can use.
     */
    public function can(string $permission): bool;

    /**
     * Id of the admin screen the person asked from, for example `admin.pages.edit`; null outside the admin.
     *
     * @return non-empty-string|null
     */
    public function screen(): ?string;
}

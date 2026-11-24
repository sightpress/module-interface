<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * An older revision of a page's text was restored as a new revision. Part of the stable event list (1.0 API).
 */
final readonly class RevisionRestored
{
    /** @var non-empty-string */
    public string $locale;

    /**
     * @param int      $pageId     the page
     * @param string   $locale     the language that was restored (BCP 47)
     * @param int      $restored   the revision that was copied
     * @param int      $revision   the new revision it became
     * @param int|null $restoredBy the person who restored it
     */
    public function __construct(
        public int $pageId,
        string $locale,
        public int $restored,
        public int $revision,
        public ?int $restoredBy,
    ) {
        Guard::notNegative($pageId, 'Page id');
        $this->locale = Guard::locale($locale, 'Page locale');
        Guard::notNegative($restored, 'Restored revision');
        Guard::notNegative($revision, 'Revision');
    }
}

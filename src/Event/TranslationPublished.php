<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A translation of a published page was published, or made a draft again. Part of the stable event list (1.0 API).
 */
final readonly class TranslationPublished
{
    /** @var non-empty-string */
    public string $locale;

    /**
     * @param int      $pageId    the page
     * @param string   $locale    the translation's language (BCP 47)
     * @param bool     $published true when it is now on the site, false when it went back to draft
     * @param int      $revision  the translation's revision
     * @param int|null $actorId   the person who did it
     */
    public function __construct(
        public int $pageId,
        string $locale,
        public bool $published,
        public int $revision,
        public ?int $actorId,
    ) {
        Guard::notNegative($pageId, 'Page id');
        $this->locale = Guard::locale($locale, 'Translation locale');
        Guard::notNegative($revision, 'Revision');
    }
}

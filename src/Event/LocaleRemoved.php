<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * A language was removed from the site. Modules that keep text per language forget that language here.
 * Part of the stable event list (1.0 API).
 */
final readonly class LocaleRemoved
{
    /** @var non-empty-string */
    public string $locale;

    /**
     * @param string   $locale  the language that was removed (BCP 47)
     * @param int|null $actorId the person who removed it
     */
    public function __construct(
        string $locale,
        public ?int $actorId,
    ) {
        $this->locale = Guard::locale($locale, 'Removed locale');
    }
}

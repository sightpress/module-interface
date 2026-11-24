<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * The site switched to another theme. Part of the stable event list (1.0 API).
 */
final readonly class ThemeApplied
{
    /**
     * @param string      $themeId  the theme now in use
     * @param string      $name     its name
     * @param string|null $previous the theme before; null when the site used the default theme
     * @param int|null    $actorId  the person who switched it; null for the command line
     */
    public function __construct(
        public string $themeId,
        public string $name,
        public ?string $previous,
        public ?int $actorId,
    ) {
        Guard::notBlank($themeId, 'Theme id');
    }
}

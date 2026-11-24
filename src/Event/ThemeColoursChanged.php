<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * The site's own colours for its theme changed. Part of the stable event list (1.0 API).
 */
final readonly class ThemeColoursChanged
{
    /** @var list<string> */
    public array $colours;

    /**
     * @param string       $themeId the theme
     * @param string       $name    its name
     * @param list<string> $colours the colours that now differ from the theme's, as `light.primary`; empty after
     *                              going back to the theme's colours
     * @param int|null     $actorId the person who changed them
     */
    public function __construct(
        public string $themeId,
        public string $name,
        array $colours,
        public ?int $actorId,
    ) {
        Guard::notBlank($themeId, 'Theme id');
        $this->colours = Guard::listOfStrings($colours, 'Changed colours');
    }
}

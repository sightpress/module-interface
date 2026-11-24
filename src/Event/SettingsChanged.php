<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Internal\Guard;

/**
 * Site settings were saved. Part of the stable event list (1.0 API).
 */
final readonly class SettingsChanged
{
    /** @var list<string> */
    public array $keys;

    /**
     * @param list<string> $keys    the settings that changed, for example `site.name` or `events.page_size`
     * @param int|null     $actorId the person who changed them; null for the command line
     */
    public function __construct(
        array $keys,
        public ?int $actorId,
    ) {
        $this->keys = Guard::listOfStrings($keys, 'Changed settings');
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->keys, true);
    }
}

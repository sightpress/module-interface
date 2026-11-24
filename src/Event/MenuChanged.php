<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * An entry of one of the site's menus was added, removed, moved or changed. Part of the stable event list (1.0 API).
 */
final readonly class MenuChanged
{
    public const string ADDED = 'added';
    public const string REMOVED = 'removed';
    public const string MOVED_UP = 'moved_up';
    public const string MOVED_DOWN = 'moved_down';
    public const string INDENTED = 'indented';
    public const string OUTDENTED = 'outdented';
    public const string CHANGED = 'changed';

    private const array CHANGES = [self::ADDED, self::REMOVED, self::MOVED_UP, self::MOVED_DOWN, self::INDENTED, self::OUTDENTED, self::CHANGED];

    /**
     * @param string      $menu    the menu's name, for example `main` or `footer`
     * @param string      $change  one of the constants of this class
     * @param int         $itemId  the menu entry
     * @param int|null    $pageId  the page it links to; null for a link to an address
     * @param string|null $url     the address it links to; null for a link to a page
     * @param int|null    $actorId the person who changed it
     */
    public function __construct(
        public string $menu,
        public string $change,
        public int $itemId,
        public ?int $pageId,
        public ?string $url,
        public ?int $actorId,
    ) {
        Guard::matches($menu, Guard::DOTTED_NAME, 'Menu name', 'use a lower-case name such as "main"');
        if (!in_array($change, self::CHANGES, true)) {
            throw new InvalidValueException(sprintf('Menu change is not valid ("%s"): use one of %s.', $change, implode(', ', self::CHANGES)));
        }
        Guard::notNegative($itemId, 'Menu entry id');
    }
}

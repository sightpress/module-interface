<?php

declare(strict_types=1);

namespace SightPress\Module\Admin;

use SightPress\Module\Access\Permission;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One entry in the admin navigation, linking to a named route.
 *
 * Core renders the menu as a `<nav>` landmark with nested lists, marks the current entry with
 * `aria-current="page"`, and hides entries whose permission the user doesn't hold.
 */
final readonly class AdminMenuItem
{
    /** @var non-empty-string */
    public string $id;

    /** @var non-empty-string */
    public string $label;

    /** @var non-empty-string */
    public string $route;

    /** @var non-empty-string|null */
    public ?string $permission;

    /** @var non-empty-string|null */
    public ?string $parent;

    /**
     * @param string      $id         unique id, for example `events` or `events.categories`
     * @param string      $label      link text in en-GB; it must make sense on its own (it is read out of context
     *                                by screen readers), and core translates it with the module's translations
     * @param string      $route      name of the route the entry links to
     * @param string|null $permission permission needed to see the entry; null shows it to every admin user
     * @param string|null $parent     id of the parent entry, or null for a top-level entry
     * @param int         $position   lower comes first; entries with the same position are sorted by label
     */
    public function __construct(
        string $id,
        string $label,
        string $route,
        ?string $permission = null,
        ?string $parent = null,
        public int $position = 0,
    ) {
        $rule = 'use lower-case words joined by dots, for example "events.categories"';
        $this->id = Guard::matches($id, Guard::DOTTED_NAME, 'Admin menu item id', $rule);
        $this->label = Guard::notBlank($label, sprintf('Label of admin menu item "%s"', $id));
        $this->route = Guard::matches(
            $route,
            Guard::DOTTED_NAME,
            sprintf('Route of admin menu item "%s"', $id),
            $rule,
        );
        $this->permission = $permission === null ? null : Guard::matches(
            $permission,
            Permission::NAME_PATTERN,
            sprintf('Permission of admin menu item "%s"', $id),
            'use a permission name such as "events.manage"',
        );
        $this->parent = $parent === null ? null : Guard::matches(
            $parent,
            Guard::DOTTED_NAME,
            sprintf('Parent of admin menu item "%s"', $id),
            $rule,
        );

        if ($this->parent === $this->id) {
            throw new InvalidValueException(sprintf('Admin menu item "%s" must not be its own parent.', $id));
        }
    }
}

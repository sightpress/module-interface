<?php

declare(strict_types=1);

namespace SightPress\Module\Access;

use SightPress\Module\Internal\Guard;

/**
 * Something a role can be allowed to do, for example `page.publish`.
 *
 * Core adds it to the site's permission catalogue and grants it to the default roles listed here when a site is
 * created or the module is turned on. Site admins can change the grants afterwards.
 */
final readonly class Permission
{
    /**
     * At least two lower-case parts joined by dots: the area, then the action (`events.manage`, `page.edit.own`).
     */
    public const string NAME_PATTERN = '/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+$/';

    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $label;

    /** @var list<non-empty-string> */
    public array $defaultRoles;

    /**
     * @param string       $name         see {@see NAME_PATTERN}
     * @param string       $label        short en-GB text shown in the admin, for example "Publish pages"; core
     *                                   translates it with the module's translations
     * @param string       $description  optional en-GB sentence that explains what the permission allows
     * @param list<string> $defaultRoles role names that get it by default, for example `admin`, `editor`
     */
    public function __construct(
        string $name,
        string $label,
        public string $description = '',
        array $defaultRoles = [],
    ) {
        $this->name = Guard::matches(
            $name,
            self::NAME_PATTERN,
            'Permission name',
            'use at least two lower-case words joined by dots, for example "events.manage"',
        );
        $this->label = Guard::notBlank($label, sprintf('Label of permission "%s"', $name));

        $roles = [];
        foreach (Guard::listOfStrings($defaultRoles, sprintf('Default roles of "%s"', $name)) as $role) {
            $roles[] = Guard::matches(
                $role,
                '/^[a-z][a-z0-9_]*$/',
                sprintf('Default role of permission "%s"', $name),
                'use lower-case letters, digits and "_", for example "editor"',
            );
        }
        Guard::unique($roles, sprintf('Default roles of "%s"', $name));
        $this->defaultRoles = $roles;
    }
}

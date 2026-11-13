<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Access\Permission;

/**
 * Adds permissions to the site's permission catalogue.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface PermissionRegistrar
{
    public function add(Permission $permission): void;
}

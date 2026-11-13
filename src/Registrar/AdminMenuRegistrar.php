<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Admin\AdminMenuItem;

/**
 * Adds entries to the admin navigation. Core shows an entry only to users who hold its permission.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface AdminMenuRegistrar
{
    public function add(AdminMenuItem $item): void;
}

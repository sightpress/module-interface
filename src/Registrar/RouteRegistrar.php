<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Routing\Route;

/**
 * Adds HTTP routes. A route of a module that is off for a site answers 404 on that site.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface RouteRegistrar
{
    public function add(Route $route): void;
}

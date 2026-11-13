<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\RouteRegistrar;
use SightPress\Module\Routing\Route;

final class InMemoryRouteRegistrar implements RouteRegistrar
{
    /** @var array<string, Route> */
    public array $routes = [];

    public function add(Route $route): void
    {
        if (isset($this->routes[$route->name])) {
            throw DuplicateRegistration::of('Route', $route->name);
        }
        $this->routes[$route->name] = $route;
    }
}

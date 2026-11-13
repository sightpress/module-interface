<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use Closure;

/**
 * Adds services to the site's service container.
 *
 * Services are private to their module unless registered as public. A module may use another module's public
 * services only if its manifest requires that module.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface ServiceRegistrar
{
    /**
     * Registers a service built by a factory. The factory is called once per site container, the first time the
     * service is needed, and gets the container (`Psr\Container\ContainerInterface`) as its only argument.
     *
     * @param non-empty-string $id      service id; use the class or interface name where there is one
     * @param Closure          $factory `Closure(Psr\Container\ContainerInterface): object`
     */
    public function factory(string $id, Closure $factory, bool $public = false): void;

    /**
     * Registers a class that core builds itself, passing other services to its constructor by type.
     *
     * @param class-string           $class
     * @param non-empty-string|null  $id    service id; defaults to the class name
     */
    public function autowire(string $class, ?string $id = null, bool $public = false): void;
}

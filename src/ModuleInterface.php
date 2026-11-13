<?php

declare(strict_types=1);

namespace SightPress\Module;

/**
 * A SightPress module: core's own features and third-party extensions alike.
 *
 * Core reads the manifest first, checks dependencies and the per-site on/off state, and only then calls
 * {@see register()} for modules that are on, in dependency order. A module that is off registers nothing.
 *
 * Implementations must be cheap to build (no I/O in the constructor) and must not keep state between calls:
 * core may call {@see register()} once per site.
 */
interface ModuleInterface
{
    /**
     * Who the module is and what it needs. Must return the same manifest every time.
     */
    public function manifest(): ModuleManifest;

    /**
     * Declares everything the module adds (services, routes, block types, permissions …) through the registrar.
     *
     * This is the only way a module adds behaviour. It must only declare things: no database queries, HTTP calls
     * or output here, because it runs while core builds the site's container.
     */
    public function register(ModuleRegistrar $registrar): void;
}

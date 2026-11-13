<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Console\Command;

/**
 * Adds commands to the `bin/sightpress` console.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface CommandRegistrar
{
    /**
     * @param class-string<Command> $command core builds it from the site's container
     */
    public function add(string $command): void;
}

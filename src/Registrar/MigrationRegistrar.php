<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Migration\MigrationProvider;

/**
 * Adds the module's database migrations. Each module keeps its migrations in its own namespace.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface MigrationRegistrar
{
    public function add(MigrationProvider $provider): void;
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Migration\MigrationProvider;
use SightPress\Module\Registrar\MigrationRegistrar;

final class InMemoryMigrationRegistrar implements MigrationRegistrar
{
    /** @var array<string, MigrationProvider> keyed by namespace */
    public array $providers = [];

    public function add(MigrationProvider $provider): void
    {
        if (isset($this->providers[$provider->namespace()])) {
            throw DuplicateRegistration::of('Migration namespace', $provider->namespace());
        }
        $this->providers[$provider->namespace()] = $provider;
    }
}

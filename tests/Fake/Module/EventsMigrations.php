<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Migration\MigrationProvider;

final class EventsMigrations implements MigrationProvider
{
    public function namespace(): string
    {
        return 'Acme\Events\Migrations';
    }

    public function directory(): string
    {
        return __DIR__ . '/migrations';
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\ViewRegistrar;

final class InMemoryViewRegistrar implements ViewRegistrar
{
    /** @var array<string, string> namespace => directory */
    public array $paths = [];

    public function add(string $namespace, string $directory): void
    {
        if (isset($this->paths[$namespace])) {
            throw DuplicateRegistration::of('View namespace', $namespace);
        }
        $this->paths[$namespace] = $directory;
    }
}

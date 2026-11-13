<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Console\Command;
use SightPress\Module\Registrar\CommandRegistrar;

final class InMemoryCommandRegistrar implements CommandRegistrar
{
    /** @var list<class-string<Command>> */
    public array $commands = [];

    public function add(string $command): void
    {
        if (in_array($command, $this->commands, true)) {
            throw DuplicateRegistration::of('Command', $command);
        }
        $this->commands[] = $command;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Assistant\AssistantProvider;
use SightPress\Module\Assistant\AssistantTask;
use SightPress\Module\Registrar\AssistantRegistrar;

final class InMemoryAssistantRegistrar implements AssistantRegistrar
{
    /** @var list<class-string<AssistantProvider>> */
    public array $providers = [];

    /** @var list<class-string<AssistantTask>> */
    public array $tasks = [];

    public function addProvider(string $provider): void
    {
        if (in_array($provider, $this->providers, true)) {
            throw DuplicateRegistration::of('Assistant provider', $provider);
        }
        $this->providers[] = $provider;
    }

    public function addTask(string $task): void
    {
        if (in_array($task, $this->tasks, true)) {
            throw DuplicateRegistration::of('Assistant task', $task);
        }
        $this->tasks[] = $task;
    }
}

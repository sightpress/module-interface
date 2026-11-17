<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Assistant\AssistantProvider;
use SightPress\Module\Assistant\AssistantTask;

/**
 * Adds providers and tasks to the optional AI assistant. They are used only on sites where the assistant is on
 * and an admin chose a provider; registering them contacts nobody.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface AssistantRegistrar
{
    /**
     * @param class-string<AssistantProvider> $provider core builds it from the site's container
     */
    public function addProvider(string $provider): void;

    /**
     * @param class-string<AssistantTask> $task core builds it from the site's container
     */
    public function addTask(string $task): void;
}

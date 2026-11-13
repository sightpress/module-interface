<?php

declare(strict_types=1);

namespace SightPress\Module\Console;

/**
 * The parsed command line, as declared by {@see Command::parameters()}.
 *
 * Implemented by core only; modules use it but must not implement it, so methods can be added in a minor release.
 */
interface CommandInput
{
    /**
     * Value of an argument, or null when it wasn't given.
     */
    public function argument(string $name): ?string;

    /**
     * Value of an option, or null when it wasn't given.
     */
    public function option(string $name): ?string;

    /**
     * Whether a flag was given.
     */
    public function flag(string $name): bool;
}

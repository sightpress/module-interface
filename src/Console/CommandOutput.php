<?php

declare(strict_types=1);

namespace SightPress\Module\Console;

/**
 * Where a command writes its messages.
 *
 * Write plain sentences that make sense when read aloud by a screen reader: no ASCII-art tables, spinners or
 * progress bars that redraw the line, and never use colour as the only way to tell success from failure. Never
 * write secrets (passwords, keys, database credentials).
 *
 * Implemented by core only; modules use it but must not implement it, so methods can be added in a minor release.
 */
interface CommandOutput
{
    /**
     * Writes one line to standard output.
     */
    public function line(string $text = ''): void;

    /**
     * Writes one line to standard error.
     */
    public function error(string $text): void;
}

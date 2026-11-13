<?php

declare(strict_types=1);

namespace SightPress\Module\Console;

/**
 * A `bin/sightpress` console command added by a module.
 *
 * Every task a command does should also be possible in the admin where it makes sense, and the other way round
 * for operator tasks; a command must ask for confirmation (or need `--force`) before destroying data.
 */
interface Command
{
    public const int SUCCESS = 0;
    public const int FAILURE = 1;
    public const int INVALID = 2;

    /**
     * Command name: lower-case words joined by `:`, starting with the module's area, for example `events:import`.
     *
     * @return non-empty-string
     */
    public function name(): string;

    /**
     * One plain en-GB sentence for the command list and help.
     *
     * @return non-empty-string
     */
    public function description(): string;

    /**
     * @return list<CommandParameter>
     */
    public function parameters(): array;

    /**
     * Runs the command and returns the exit code: {@see SUCCESS}, {@see FAILURE} or {@see INVALID} (bad input).
     */
    public function run(CommandInput $input, CommandOutput $output): int;
}

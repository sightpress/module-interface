<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Console\Command;
use SightPress\Module\Console\CommandInput;
use SightPress\Module\Console\CommandOutput;
use SightPress\Module\Console\CommandParameter;
use SightPress\Module\Console\CommandParameterType;

final class ImportEventsCommand implements Command
{
    public function name(): string
    {
        return 'events:import';
    }

    public function description(): string
    {
        return 'Imports events from an iCalendar file.';
    }

    public function parameters(): array
    {
        return [
            new CommandParameter('file', 'Path to the .ics file.', required: true),
            new CommandParameter('locale', 'Locale of the imported events.', CommandParameterType::Option),
            new CommandParameter('dry-run', 'Show what would be imported without saving.', CommandParameterType::Flag),
        ];
    }

    public function run(CommandInput $input, CommandOutput $output): int
    {
        $file = $input->argument('file');
        if ($file === null) {
            $output->error('Give the path to an .ics file.');

            return self::INVALID;
        }

        $output->line(sprintf(
            '%s 0 events from %s (locale %s).',
            $input->flag('dry-run') ? 'Would import' : 'Imported',
            $file,
            $input->option('locale') ?? 'en-GB',
        ));

        return self::SUCCESS;
    }
}

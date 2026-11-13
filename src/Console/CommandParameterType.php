<?php

declare(strict_types=1);

namespace SightPress\Module\Console;

/**
 * How a command parameter is given on the command line.
 */
enum CommandParameterType: string
{
    /** A positional value: `site:create demo`. */
    case Argument = 'argument';

    /** A named value: `--locale=en-GB`. */
    case Option = 'option';

    /** A named switch without a value: `--force`. Never required. */
    case Flag = 'flag';
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Console;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One argument, option or flag of a console command, shown in the command's help.
 */
final readonly class CommandParameter
{
    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $description;

    /**
     * @param string               $name        lower-case letters, digits and `-`, without leading dashes
     * @param string               $description one plain en-GB sentence for the help text
     * @param CommandParameterType $type        argument, option or flag
     * @param bool                 $required    whether the command refuses to run without it; flags can't be
     *                                          required
     */
    public function __construct(
        string $name,
        string $description,
        public CommandParameterType $type = CommandParameterType::Argument,
        public bool $required = false,
    ) {
        $this->name = Guard::matches(
            $name,
            '/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/',
            'Command parameter name',
            'use lower-case words joined by "-", without leading dashes, for example "admin-email"',
        );
        $this->description = Guard::notBlank($description, sprintf('Description of parameter "%s"', $name));

        if ($type === CommandParameterType::Flag && $required) {
            throw new InvalidValueException(sprintf('Flag "--%s" can\'t be required.', $name));
        }
    }
}

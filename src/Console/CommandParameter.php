<?php

declare(strict_types=1);

namespace SightPress\Module\Console;

use Closure;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One argument, option or flag of a console command, shown in the command's help and used as a question when
 * someone runs the command at a terminal.
 *
 * How core asks: when a required parameter is missing (or a given value is not accepted) and someone is at the
 * terminal, core asks for every parameter that {@see $ask}s, in the declared order, skipping the ones whose
 * {@see $when} says they don't apply. Each question shows its default (pressing Enter takes it) and its choices.
 * An answer that is not accepted is explained and asked again, a few times at most, before the command stops.
 * An answer with a {@see $describe}d meaning is read back in plain words for the person to confirm. At the end,
 * core lists every answer (hiding {@see $secret} ones) and asks whether it is right; if not, it starts again
 * from the first question, with the earlier answers as defaults. Without a terminal (or with `--no-interaction`)
 * nothing is asked: defaults, {@see $when} and {@see $normalise} still apply, and a value that is not accepted
 * stops the command with the same explanation.
 */
final readonly class CommandParameter
{
    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $description;

    /** @var non-empty-string */
    public string $label;

    /** @var list<non-empty-string> */
    public array $choices;

    public bool $ask;

    /**
     * @param string                                       $name        lower-case letters, digits and `-`, without
     *                                                                  leading dashes
     * @param string                                       $description one plain en-GB sentence for the help text;
     *                                                                  also the question asked at the terminal
     * @param CommandParameterType                         $type        argument, option or flag
     * @param bool                                         $required    whether the command refuses to run without
     *                                                                  it (only while {@see $when} applies); flags
     *                                                                  can't be required
     * @param string|null                                  $label       a few en-GB words naming the value in the
     *                                                                  list of answers, for example "Main
     *                                                                  language" (default: the description without
     *                                                                  its final colon or full stop)
     * @param string|(Closure(CommandInput): ?string)|null $default     value used when none is given; a closure
     *                                                                  works it out from the answers so far. For a
     *                                                                  flag it is "yes" or "no" and only used as
     *                                                                  the answer to its question
     * @param list<string>                                 $choices     the only accepted answers, matched without
     *                                                                  regard to case; the answer becomes the
     *                                                                  choice as written here
     * @param (Closure(string, CommandInput): string)|null $normalise   turns a non-empty answer into the value the
     *                                                                  command gets, given the answers so far, or
     *                                                                  throws an `InvalidArgumentException` whose
     *                                                                  message tells the person, in one plain
     *                                                                  sentence, what to type instead. Must give
     *                                                                  the same value when given its own result
     * @param (Closure(string): ?string)|null              $describe    what the normalised value means in plain
     *                                                                  en-GB words (for example "British English"
     *                                                                  for `en-GB`), read back for the person to
     *                                                                  confirm; null when there is nothing to add
     * @param (Closure(CommandInput): bool)|null           $when        whether the parameter applies, given the
     *                                                                  answers so far; one that doesn't apply is
     *                                                                  neither asked nor required, and the command
     *                                                                  gets no value for it
     * @param bool                                         $secret      a password or a key: typed without being
     *                                                                  shown, typed twice to check the two
     *                                                                  match, and never printed back
     * @param bool|null                                    $ask         whether it is asked at the terminal
     *                                                                  (default: when it is required)
     */
    public function __construct(
        string $name,
        string $description,
        public CommandParameterType $type = CommandParameterType::Argument,
        public bool $required = false,
        ?string $label = null,
        public string|Closure|null $default = null,
        array $choices = [],
        public ?Closure $normalise = null,
        public ?Closure $describe = null,
        public ?Closure $when = null,
        public bool $secret = false,
        ?bool $ask = null,
    ) {
        $this->name = Guard::matches(
            $name,
            '/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/',
            'Command parameter name',
            'use lower-case words joined by "-", without leading dashes, for example "admin-email"',
        );
        $this->description = Guard::notBlank($description, sprintf('Description of parameter "%s"', $name));
        $this->label = Guard::notBlank(
            $label ?? rtrim(trim($description), ':.'),
            sprintf('Label of parameter "%s"', $name),
        );
        $this->choices = self::choices($choices, $name);
        $this->ask = $ask ?? $required;

        if ($type === CommandParameterType::Flag) {
            if ($required) {
                throw new InvalidValueException(sprintf('Flag "--%s" can\'t be required.', $name));
            }
            if ($default !== null && !in_array($default, ['yes', 'no'], true)) {
                throw new InvalidValueException(sprintf('The default of flag "--%s" must be "yes" or "no".', $name));
            }
            if ($this->choices !== [] || $normalise !== null || $describe !== null || $secret) {
                throw new InvalidValueException(sprintf(
                    'Flag "--%s" is answered yes or no: it can\'t have choices, a normaliser, a description or be secret.',
                    $name,
                ));
            }
        }
        if (is_string($default) && $this->choices !== [] && !in_array($default, $this->choices, true)) {
            throw new InvalidValueException(sprintf('The default of parameter "%s" must be one of its choices.', $name));
        }
    }

    /**
     * @param array<mixed> $choices
     *
     * @return list<non-empty-string>
     */
    private static function choices(array $choices, string $name): array
    {
        $field = sprintf('Choices of parameter "%s"', $name);
        $list = [];
        foreach (Guard::listOfStrings($choices, $field) as $choice) {
            $list[] = Guard::notBlank($choice, $field);
        }
        Guard::unique(array_map(mb_strtolower(...), $list), $field);

        return $list;
    }
}

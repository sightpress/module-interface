<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * A checked answer, ready to show with *Insert*, *Copy*, *Try again* and *Discard*. Nothing is saved: *Insert*
 * fills the form as an unsaved edit, so validation, permissions, workflow and publish checks still apply.
 */
final readonly class AssistantSuggestion
{
    /** @var non-empty-string */
    public string $text;

    /** @var array<non-empty-string, mixed> */
    public array $fields;

    /**
     * @param string               $text   what the person reads and copies: plain text or Markdown, already
     *                                     checked by the task
     * @param array<string, mixed> $fields form field name => value that *Insert* puts in the form, for example
     *                                     `['summary' => '…']` or `['blocks' => [ … ]]` (block data already
     *                                     validated against the block schemas); empty when there is nothing to
     *                                     insert (answers to questions)
     */
    public function __construct(string $text, array $fields = [])
    {
        $this->text = Guard::notBlank($text, 'Suggestion text');
        foreach (array_keys($fields) as $name) {
            if (!is_string($name) || trim($name) === '') {
                throw new InvalidValueException('Suggestion field names must be non-empty strings.');
            }
        }
        /** @var array<non-empty-string, mixed> $fields */
        $this->fields = $fields;
    }
}

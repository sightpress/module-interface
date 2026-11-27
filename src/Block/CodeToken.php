<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

use SightPress\Module\Exception\InvalidValueException;

/**
 * A piece of code and what it is, as a {@see CodeHighlighter} returns it (since 0.6). Plain text: core escapes it.
 */
final readonly class CodeToken
{
    /** @var non-empty-string */
    public string $text;

    /**
     * @param string             $text the code, exactly as written
     * @param CodeTokenKind|null $kind what it is; null for plain text
     */
    public function __construct(string $text, public ?CodeTokenKind $kind = null)
    {
        if ($text === '') {
            throw new InvalidValueException('A code token must hold some text.');
        }
        $this->text = $text;
    }
}

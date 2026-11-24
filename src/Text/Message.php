<?php

declare(strict_types=1);

namespace SightPress\Module\Text;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * Text for a person to read, translated by core into the reader's language when it is shown: screen titles and
 * notices, for example. `{name}` placeholders are filled from the parameters after translation.
 *
 * Put a module's texts in its own translation domain (the file name prefix of its XLIFF files, registered with
 * {@see \SightPress\Module\Registrar\TranslationRegistrar}); `messages` is core's.
 */
final readonly class Message
{
    /** @var non-empty-string */
    public string $text;

    /** @var array<string, string|int|float> */
    public array $parameters;

    /** @var non-empty-string */
    public string $domain;

    /**
     * @param string                          $text       the source text (en-GB), for example `Saved {title}.`
     * @param array<string, string|int|float> $parameters values for the placeholders, by name without braces
     * @param string                          $domain     translation domain
     */
    public function __construct(string $text, array $parameters = [], string $domain = 'messages')
    {
        $this->text = Guard::notBlank($text, 'Message text');
        foreach ($parameters as $name => $value) {
            if (!is_string($name) || !(is_string($value) || is_int($value) || is_float($value))) {
                throw new InvalidValueException(sprintf('Parameters of message "%s" must map names to text or numbers.', $text));
            }
        }
        /** @var array<string, string|int|float> $parameters */
        $this->parameters = $parameters;
        $this->domain = Guard::matches($domain, '/^[a-z][a-z0-9_]*$/', 'Translation domain', 'use lower-case letters, digits and "_", for example "acme_events"');
    }
}

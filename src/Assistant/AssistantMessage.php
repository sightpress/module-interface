<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One message of an {@see AssistantRequest}.
 *
 * Tasks must put page content, file names and anything else people typed between clear delimiters and say in
 * the system message that it is data, never instructions (prompt-injection hardening).
 */
final readonly class AssistantMessage
{
    /** @var non-empty-string */
    public string $text;

    /** @var list<AssistantImage> */
    public array $images;

    /**
     * @param AssistantRole        $role   who it comes from
     * @param string               $text   the message text
     * @param list<AssistantImage> $images images sent with it; only for {@see AssistantRole::User} messages
     */
    public function __construct(public AssistantRole $role, string $text, array $images = [])
    {
        $this->text = Guard::notBlank($text, sprintf('Text of %s message', $role->value));
        $this->images = Guard::listOf($images, AssistantImage::class, sprintf('Images of %s message', $role->value));

        if ($this->images !== [] && $role !== AssistantRole::User) {
            throw new InvalidValueException(sprintf('Only user messages can have images, not %s messages.', $role->value));
        }
    }

    public static function system(string $text): self
    {
        return new self(AssistantRole::System, $text);
    }

    /**
     * @param list<AssistantImage> $images
     */
    public static function user(string $text, array $images = []): self
    {
        return new self(AssistantRole::User, $text, $images);
    }

    public static function assistant(string $text): self
    {
        return new self(AssistantRole::Assistant, $text);
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Exception;

use RuntimeException;
use SightPress\Module\Assistant\AssistantFailure;
use Throwable;

/**
 * Thrown by assistant providers and tasks when a call can't give a usable suggestion.
 *
 * The message is for the log only: it must never contain the API key, the prompt or the answer text. People see a
 * plain-language message that core picks from {@see $failure}.
 */
final class AssistantException extends RuntimeException
{
    public function __construct(public readonly AssistantFailure $failure, string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message === '' ? sprintf('Assistant call failed: %s.', $failure->value) : $message, 0, $previous);
    }
}

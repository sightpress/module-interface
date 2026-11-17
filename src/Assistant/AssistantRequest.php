<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * Everything a provider sends to its model for one answer. Core builds it from the task's prompt and the site's
 * assistant settings.
 */
final readonly class AssistantRequest
{
    /** @var non-empty-string */
    public string $model;

    /** @var non-empty-list<AssistantMessage> */
    public array $messages;

    /** @var positive-int */
    public int $maxOutputTokens;

    /**
     * @param string                 $model           the provider's name for the model, for example one from
     *                                                {@see AssistantProvider::models()}
     * @param list<AssistantMessage> $messages        system messages first (if any), then the conversation,
     *                                                ending with a user message
     * @param int                    $maxOutputTokens most tokens the answer may use
     * @param float|null             $temperature     0 (most predictable) to 1 (most varied); null for the
     *                                                provider's default. Providers scale it to their own range
     */
    public function __construct(
        string $model,
        array $messages,
        int $maxOutputTokens = 1024,
        public ?float $temperature = null,
    ) {
        $this->model = Guard::notBlank($model, 'Assistant model');
        $messages = Guard::listOf($messages, AssistantMessage::class, 'Assistant messages');
        if ($messages === []) {
            throw new InvalidValueException('An assistant request needs at least one message.');
        }
        $this->messages = $messages;

        $conversationStarted = false;
        foreach ($messages as $index => $message) {
            if ($message->role !== AssistantRole::System) {
                $conversationStarted = true;
            } elseif ($conversationStarted) {
                throw new InvalidValueException(sprintf(
                    'Assistant message %d is a system message after the conversation started; put system messages first.',
                    $index,
                ));
            }
        }
        if ($messages[array_key_last($messages)]->role !== AssistantRole::User) {
            throw new InvalidValueException('The last assistant message must be a user message.');
        }

        if ($maxOutputTokens < 1) {
            throw new InvalidValueException(sprintf('Most output tokens must be 1 or more (%d).', $maxOutputTokens));
        }
        $this->maxOutputTokens = $maxOutputTokens;

        if ($temperature !== null && !($temperature >= 0.0 && $temperature <= 1.0)) {
            throw new InvalidValueException(sprintf('Temperature must be between 0 and 1 (%s).', var_export($temperature, true)));
        }
    }

    /**
     * The system messages joined into one text (empty when there are none), for providers that take the
     * instructions apart from the conversation.
     */
    public function instructions(): string
    {
        $system = array_filter($this->messages, static fn (AssistantMessage $m): bool => $m->role === AssistantRole::System);

        return implode("\n\n", array_map(static fn (AssistantMessage $m): string => $m->text, $system));
    }

    /**
     * The messages after the system messages.
     *
     * @return non-empty-list<AssistantMessage>
     */
    public function conversation(): array
    {
        /** @var non-empty-list<AssistantMessage> $conversation the last message is always a user message */
        $conversation = array_values(array_filter(
            $this->messages,
            static fn (AssistantMessage $m): bool => $m->role !== AssistantRole::System,
        ));

        return $conversation;
    }

    /**
     * Whether any message has an image, so core only sends it to a model that can see images.
     */
    public function hasImages(): bool
    {
        foreach ($this->messages as $message) {
            if ($message->images !== []) {
                return true;
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Exception\AssistantException;

/**
 * One thing the assistant can help with, for example a page summary or alt text for an image. Core's tasks and
 * modules' tasks are added with `ModuleRegistrar::assistant()->addTask()`; core builds them from the site's
 * container.
 *
 * A task only writes a prompt and checks the answer. It has no way to change the site: the result is always a
 * {@see AssistantSuggestion} that a person inserts, copies or discards.
 */
interface AssistantTask
{
    /**
     * Unique id: lower-case words joined by `_`, with a vendor prefix and a dot for third-party tasks, for example
     * `summary` or `acme.event_description`.
     *
     * @return non-empty-string
     */
    public function id(): string;

    /**
     * Name of the task in en-GB, used on its button, for example "Suggest a summary". It must make sense on its
     * own. Core translates it with the module's translations.
     *
     * @return non-empty-string
     */
    public function label(): string;

    /**
     * Permission needed to use the task, besides `assistant.use`, for example `page.edit`.
     *
     * @return non-empty-string
     */
    public function permission(): string;

    /**
     * JSON Schema (draft 2020-12) of the task's input, as a PHP array. Core validates the input against it before
     * {@see buildPrompt()}.
     *
     * @return array<string, mixed>
     */
    public function inputSchema(): array;

    /**
     * Whether the task sends images, so it is only offered with a model that has
     * {@see ProviderCapabilities::$vision}.
     */
    public function needsVision(): bool;

    /**
     * The messages to send. Only what the person chose to share may go in (the current page, block or image);
     * never user lists, email addresses, audit data or other pages. Content goes between clear delimiters and is
     * described as data in the system message.
     *
     * @param array<string, mixed> $input valid against {@see inputSchema()}
     *
     * @return non-empty-list<AssistantMessage>
     */
    public function buildPrompt(array $input, AssistantContext $context): array;

    /**
     * Checks the answer like user input and turns it into a suggestion: rich text through the sanitiser, block
     * data against the block schemas, lengths within their limits.
     *
     * @param array<string, mixed> $input the same input as {@see buildPrompt()}
     *
     * @throws AssistantException with {@see AssistantFailure::InvalidOutput} when the answer can't be used
     */
    public function validateOutput(AssistantResponse $response, array $input, AssistantContext $context): AssistantSuggestion;
}

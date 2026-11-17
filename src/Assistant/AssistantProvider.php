<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use SightPress\Module\Exception\AssistantException;

/**
 * An AI service the optional assistant can use, with the site's own key (bring your own key). Core ships
 * providers for the best-known services and any OpenAI-compatible endpoint; modules add theirs with
 * `ModuleRegistrar::assistant()->addProvider()`.
 *
 * A provider only turns an {@see AssistantRequest} into an {@see AssistantResponse}. It has no tools and no access
 * to anything else: it must not change the site, and it must not log, store or forward the key, the prompt or the
 * answer. It must use a timeout and must not retry forever.
 */
interface AssistantProvider
{
    /**
     * Unique id stored in the site's settings: lower-case words joined by `_`, with a vendor prefix and a dot for
     * third-party providers, for example `anthropic` or `acme.private_ai`.
     *
     * @return non-empty-string
     */
    public function id(): string;

    /**
     * Name shown to site admins, for example "Mistral AI".
     *
     * @return non-empty-string
     */
    public function label(): string;

    /**
     * Plain en-GB sentences shown before an admin chooses the provider: who runs it, where requests are
     * processed and how long the provider keeps them. Core translates it with the module's translations.
     *
     * @return non-empty-string
     */
    public function description(): string;

    /**
     * Model names an admin can choose from, the recommended one first; an empty list lets the admin type any
     * name (custom endpoints). Should come from a maintained resource file, not be written in code.
     *
     * @return list<non-empty-string>
     */
    public function models(): array;

    /**
     * Whether the admin must give a base URL (custom or self-hosted endpoints).
     */
    public function needsBaseUrl(): bool;

    /**
     * What a model can do besides text.
     *
     * @param non-empty-string $model
     */
    public function capabilities(string $model): ProviderCapabilities;

    /**
     * Sends the request and waits for the whole answer.
     *
     * @throws AssistantException when there is no usable answer, with the reason
     */
    public function complete(AssistantRequest $request, AssistantConnection $connection): AssistantResponse;
}

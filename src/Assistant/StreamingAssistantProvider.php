<?php

declare(strict_types=1);

namespace SightPress\Module\Assistant;

use Generator;
use SightPress\Module\Exception\AssistantException;

/**
 * A provider that can also stream an answer as it is written. Optional: core streams only when the provider
 * implements this and {@see ProviderCapabilities::$streaming} is true for the model, and otherwise calls
 * {@see AssistantProvider::complete()}.
 *
 * Core never pushes streamed text into a live region; it announces only "Working on it…" and "Suggestion ready".
 */
interface StreamingAssistantProvider extends AssistantProvider
{
    /**
     * Yields the answer in pieces of text, then returns the whole {@see AssistantResponse} (its text is all the
     * pieces joined), so core can check it and count the tokens.
     *
     * @return Generator<int, string, mixed, AssistantResponse>
     *
     * @throws AssistantException when there is no usable answer, with the reason; also while streaming
     */
    public function stream(AssistantRequest $request, AssistantConnection $connection): Generator;
}

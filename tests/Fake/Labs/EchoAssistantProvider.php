<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Labs;

use Generator;
use SightPress\Module\Assistant\AssistantConnection;
use SightPress\Module\Assistant\AssistantFailure;
use SightPress\Module\Assistant\AssistantRequest;
use SightPress\Module\Assistant\AssistantResponse;
use SightPress\Module\Assistant\ProviderCapabilities;
use SightPress\Module\Assistant\StreamingAssistantProvider;
use SightPress\Module\Assistant\TokenUsage;
use SightPress\Module\Exception\AssistantException;

/**
 * A provider that answers with the last user message, one word per token, without contacting anyone.
 */
final class EchoAssistantProvider implements StreamingAssistantProvider
{
    public const string BAD_KEY = 'sk-revoked-0000';

    public function id(): string
    {
        return 'acme.echo';
    }

    public function label(): string
    {
        return 'Echo';
    }

    public function description(): string
    {
        return 'Runs on this server and sends nothing anywhere. Nothing is kept.';
    }

    public function models(): array
    {
        return ['echo-1', 'echo-vision-1'];
    }

    public function needsBaseUrl(): bool
    {
        return false;
    }

    public function capabilities(string $model): ProviderCapabilities
    {
        return new ProviderCapabilities(vision: str_contains($model, 'vision'), streaming: true);
    }

    public function complete(AssistantRequest $request, AssistantConnection $connection): AssistantResponse
    {
        $pieces = $this->stream($request, $connection);
        foreach ($pieces as $piece) {
            // A provider without streaming would build the text in one go.
        }

        return $pieces->getReturn();
    }

    public function stream(AssistantRequest $request, AssistantConnection $connection): Generator
    {
        if ($connection->apiKey === self::BAD_KEY) {
            throw new AssistantException(AssistantFailure::InvalidKey, 'The provider refused key ' . $connection->maskedKey() . '.');
        }
        if (!in_array($request->model, $this->models(), true)) {
            throw new AssistantException(AssistantFailure::ModelUnavailable);
        }

        $conversation = $request->conversation();
        $words = self::words('Echo: ' . $conversation[array_key_last($conversation)]->text);
        $answer = array_slice($words, 0, $request->maxOutputTokens);
        foreach ($answer as $index => $word) {
            yield ($index === 0 ? '' : ' ') . $word;
        }

        $input = 0;
        foreach ($request->messages as $message) {
            $input += count(self::words($message->text));
        }

        return new AssistantResponse(
            implode(' ', $answer),
            $request->model,
            new TokenUsage($input, count($answer)),
            count($answer) < count($words),
        );
    }

    /**
     * @return list<string>
     */
    private static function words(string $text): array
    {
        $words = preg_split('/\s+/', $text);

        return $words === false ? [] : $words;
    }
}

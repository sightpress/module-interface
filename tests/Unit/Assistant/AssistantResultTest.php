<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Assistant;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SightPress\Module\Assistant\AssistantFailure;
use SightPress\Module\Assistant\AssistantResponse;
use SightPress\Module\Assistant\AssistantSuggestion;
use SightPress\Module\Assistant\ProviderCapabilities;
use SightPress\Module\Assistant\TokenUsage;
use SightPress\Module\Exception\AssistantException;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(AssistantResponse::class)]
#[CoversClass(TokenUsage::class)]
#[CoversClass(AssistantSuggestion::class)]
#[CoversClass(ProviderCapabilities::class)]
#[CoversClass(AssistantException::class)]
#[UsesClass(Guard::class)]
final class AssistantResultTest extends TestCase
{
    public function testResponseDefaultsAndFields(): void
    {
        $response = new AssistantResponse('', 'model-a');

        self::assertSame('', $response->text);
        self::assertSame('model-a', $response->model);
        self::assertSame(0, $response->usage->total());
        self::assertFalse($response->truncated);

        $usage = new TokenUsage(120, 30);
        $full = new AssistantResponse('Hi.', 'model-a', $usage, true);
        self::assertSame($usage, $full->usage);
        self::assertTrue($full->truncated);
    }

    public function testResponseNeedsAModel(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Model of the assistant response must not be empty.');

        new AssistantResponse('Hi.', '');
    }

    public function testTokenUsageAddsUp(): void
    {
        $usage = new TokenUsage(120, 30)->plus(new TokenUsage(5, 7));

        self::assertSame(125, $usage->inputTokens);
        self::assertSame(37, $usage->outputTokens);
        self::assertSame(162, $usage->total());
    }

    public function testTokenUsageRefusesNegativeCounts(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Output tokens must not be negative (-1).');

        new TokenUsage(0, -1);
    }

    public function testSuggestion(): void
    {
        $answer = new AssistantSuggestion('How to add a language: …');
        self::assertSame('How to add a language: …', $answer->text);
        self::assertSame([], $answer->fields);

        $insert = new AssistantSuggestion('A short summary.', ['summary' => 'A short summary.']);
        self::assertSame(['summary' => 'A short summary.'], $insert->fields);
    }

    public function testSuggestionNeedsText(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Suggestion text must not be empty.');

        new AssistantSuggestion("\n");
    }

    public function testSuggestionFieldsNeedNames(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Suggestion field names must be non-empty strings.');

        /** @phpstan-ignore argument.type (testing the run-time check) */
        new AssistantSuggestion('Text', ['first value']);
    }

    public function testSuggestionFieldNamesMustNotBeBlank(): void
    {
        $this->expectException(InvalidValueException::class);

        new AssistantSuggestion('Text', [' ' => 'value']);
    }

    public function testCapabilities(): void
    {
        $none = new ProviderCapabilities();
        self::assertFalse($none->vision);
        self::assertFalse($none->streaming);

        $all = new ProviderCapabilities(true, true);
        self::assertTrue($all->vision);
        self::assertTrue($all->streaming);
    }

    public function testExceptionCarriesTheFailure(): void
    {
        $plain = new AssistantException(AssistantFailure::Timeout);
        self::assertSame(AssistantFailure::Timeout, $plain->failure);
        self::assertSame('Assistant call failed: timeout.', $plain->getMessage());

        $previous = new RuntimeException('HTTP 429');
        $detailed = new AssistantException(AssistantFailure::RateLimited, 'Provider asked to wait 30 s.', $previous);
        self::assertSame('Provider asked to wait 30 s.', $detailed->getMessage());
        self::assertSame($previous, $detailed->getPrevious());
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Assistant;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Assistant\AssistantImage;
use SightPress\Module\Assistant\AssistantMessage;
use SightPress\Module\Assistant\AssistantRequest;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(AssistantRequest::class)]
#[UsesClass(AssistantMessage::class)]
#[UsesClass(AssistantImage::class)]
#[UsesClass(Guard::class)]
final class AssistantRequestTest extends TestCase
{
    public function testDefaults(): void
    {
        $request = new AssistantRequest('model-a', [AssistantMessage::user('Hello')]);

        self::assertSame('model-a', $request->model);
        self::assertCount(1, $request->messages);
        self::assertSame(1024, $request->maxOutputTokens);
        self::assertNull($request->temperature);
        self::assertSame('', $request->instructions());
        self::assertSame($request->messages, $request->conversation());
        self::assertFalse($request->hasImages());
    }

    public function testSplitsInstructionsFromTheConversation(): void
    {
        $messages = [
            AssistantMessage::system('Be brief.'),
            AssistantMessage::system('Answer in en-GB.'),
            AssistantMessage::user('Hello'),
            AssistantMessage::assistant('Hi.'),
            AssistantMessage::user('Describe this.', [new AssistantImage('image/png', 'x')]),
        ];

        $request = new AssistantRequest('model-a', $messages, 10, 0.0);

        self::assertSame(10, $request->maxOutputTokens);
        self::assertSame(0.0, $request->temperature);
        self::assertSame("Be brief.\n\nAnswer in en-GB.", $request->instructions());
        self::assertSame(array_slice($messages, 2), $request->conversation());
        self::assertTrue($request->hasImages());
        self::assertSame(1.0, new AssistantRequest('m', [AssistantMessage::user('Hi')], temperature: 1.0)->temperature);
    }

    /**
     * @return iterable<string, array{string, list<AssistantMessage>, int, ?float, string}>
     */
    public static function invalidRequests(): iterable
    {
        $user = AssistantMessage::user('Hello');

        yield 'no model' => ['', [$user], 1, null, 'Assistant model must not be empty.'];
        yield 'no messages' => ['m', [], 1, null, 'An assistant request needs at least one message.'];
        yield 'late system message' => ['m', [$user, AssistantMessage::system('x'), $user], 1, null, 'Assistant message 1 is a system message after the conversation started'];
        yield 'ends with the assistant' => ['m', [$user, AssistantMessage::assistant('Hi')], 1, null, 'The last assistant message must be a user message.'];
        yield 'only instructions' => ['m', [AssistantMessage::system('x')], 1, null, 'The last assistant message must be a user message.'];
        yield 'no output tokens' => ['m', [$user], 0, null, 'Most output tokens must be 1 or more (0).'];
        yield 'too cold' => ['m', [$user], 1, -0.1, 'Temperature must be between 0 and 1 (-0.1).'];
        yield 'too hot' => ['m', [$user], 1, 1.5, 'Temperature must be between 0 and 1 (1.5).'];
        yield 'not a number' => ['m', [$user], 1, NAN, 'Temperature must be between 0 and 1 (NAN).'];
    }

    /**
     * @param list<AssistantMessage> $messages
     */
    #[DataProvider('invalidRequests')]
    public function testRefusesInvalidRequests(string $model, array $messages, int $max, ?float $temperature, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        new AssistantRequest($model, $messages, $max, $temperature);
    }
}

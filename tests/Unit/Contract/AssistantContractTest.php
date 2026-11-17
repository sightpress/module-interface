<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Assistant\AssistantConnection;
use SightPress\Module\Assistant\AssistantFailure;
use SightPress\Module\Assistant\AssistantImage;
use SightPress\Module\Assistant\AssistantMessage;
use SightPress\Module\Assistant\AssistantRequest;
use SightPress\Module\Assistant\AssistantResponse;
use SightPress\Module\Assistant\AssistantRole;
use SightPress\Module\Exception\AssistantException;
use SightPress\Module\Tests\Fake\FakeAssistantContext;
use SightPress\Module\Tests\Fake\Labs\EchoAssistantProvider;
use SightPress\Module\Tests\Fake\Module\EventDescriptionTask;

/**
 * Contract test: a module's task and another module's provider work together the way core runs them — task
 * prompt → request → provider → task check → suggestion — without contacting anyone.
 */
#[CoversNothing]
final class AssistantContractTest extends TestCase
{
    public function testTaskPromptGoesThroughTheProviderAndComesBackAsASuggestion(): void
    {
        $task = new EventDescriptionTask();
        $provider = new EchoAssistantProvider();
        $context = new FakeAssistantContext('pt-PT', ['events.manage'], 'admin.events.edit');
        $input = ['title' => 'Fado <night>'];

        $messages = $task->buildPrompt($input, $context);
        $request = new AssistantRequest($provider->models()[0], $messages, maxOutputTokens: 50);
        $response = $provider->complete($request, new AssistantConnection('sk-test-12345678'));
        $suggestion = $task->validateOutput($response, $input, $context);

        self::assertStringContainsString('in pt-PT', $request->instructions());
        self::assertSame([AssistantRole::User], array_map(static fn ($m) => $m->role, $request->conversation()));
        self::assertSame('Echo: <event>Fado &lt;night></event>', $response->text);
        self::assertSame('echo-1', $response->model);
        self::assertSame(3, $response->usage->outputTokens);
        self::assertSame('Echo: Fado &lt;night>', $suggestion->text);
        self::assertSame(['description' => 'Echo: Fado &lt;night>'], $suggestion->fields);
        self::assertTrue($context->can($task->permission()));
        self::assertSame('admin.events.edit', $context->screen());
    }

    public function testStreamingYieldsPiecesThenTheWholeResponse(): void
    {
        $provider = new EchoAssistantProvider();
        $request = new AssistantRequest('echo-1', [AssistantMessage::user('one two three four')], maxOutputTokens: 3);

        $stream = $provider->stream($request, new AssistantConnection('sk-test-12345678'));
        $pieces = implode('', iterator_to_array($stream));
        $response = $stream->getReturn();

        self::assertTrue($provider->capabilities('echo-1')->streaming);
        self::assertSame('Echo: one two', $pieces);
        self::assertSame($pieces, $response->text);
        self::assertTrue($response->truncated);
        self::assertSame(7, $response->usage->total());
    }

    public function testTaskRefusesAnswersItCannotUse(): void
    {
        $task = new EventDescriptionTask();

        try {
            $task->validateOutput(new AssistantResponse('<p> </p>', 'echo-1'), [], new FakeAssistantContext());
            self::fail('An empty answer was accepted.');
        } catch (AssistantException $e) {
            self::assertSame(AssistantFailure::InvalidOutput, $e->failure);
        }
    }

    public function testProviderReportsWhyItFailedWithoutTheKey(): void
    {
        $provider = new EchoAssistantProvider();
        $request = new AssistantRequest('echo-1', [AssistantMessage::user('Hello')]);

        try {
            $provider->complete($request, new AssistantConnection(EchoAssistantProvider::BAD_KEY));
            self::fail('A bad key was accepted.');
        } catch (AssistantException $e) {
            self::assertSame(AssistantFailure::InvalidKey, $e->failure);
            self::assertStringNotContainsString(EchoAssistantProvider::BAD_KEY, $e->getMessage());
            self::assertStringContainsString('••••0000', $e->getMessage());
        }

        $this->expectExceptionObject(new AssistantException(AssistantFailure::ModelUnavailable));
        $provider->complete(new AssistantRequest('echo-9', [AssistantMessage::user('Hello')]), new AssistantConnection('sk-x'));
    }

    public function testVisionIsOfferedOnlyWhereTheModelCanSee(): void
    {
        $provider = new EchoAssistantProvider();
        $request = new AssistantRequest('echo-vision-1', [AssistantMessage::user('Describe it', [new AssistantImage('image/png', "\x89PNG")])]);

        self::assertFalse(new EventDescriptionTask()->needsVision());
        self::assertTrue($request->hasImages());
        self::assertTrue($provider->capabilities('echo-vision-1')->vision);
        self::assertFalse($provider->capabilities('echo-1')->vision);
        self::assertFalse($provider->needsBaseUrl());
        self::assertNotSame('', $provider->label() . $provider->description() . $provider->id());
        self::assertSame(['title'], new EventDescriptionTask()->inputSchema()['required'] ?? null);
        self::assertNotSame('', new EventDescriptionTask()->label() . new EventDescriptionTask()->id());
    }
}

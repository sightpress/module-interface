<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Assistant;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Assistant\AssistantConnection;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(AssistantConnection::class)]
#[UsesClass(Guard::class)]
final class AssistantConnectionTest extends TestCase
{
    private const string KEY = 'sk-live-abcdefgh1234';

    public function testHoldsTheKeyAndBaseUrl(): void
    {
        $connection = new AssistantConnection(self::KEY, 'https://ai.example.org/v1');

        self::assertSame(self::KEY, $connection->apiKey);
        self::assertSame('https://ai.example.org/v1', $connection->baseUrl);
        self::assertNull(new AssistantConnection(self::KEY)->baseUrl);
        self::assertSame('http://localhost:11434', new AssistantConnection(self::KEY, 'http://localhost:11434')->baseUrl);
    }

    public function testShowsOnlyTheEndOfTheKey(): void
    {
        $connection = new AssistantConnection(self::KEY);

        self::assertSame('••••1234', $connection->maskedKey());
        self::assertSame('••••', new AssistantConnection('short')->maskedKey());
        self::assertSame(['apiKey' => '••••1234', 'baseUrl' => null], $connection->__debugInfo());
        self::assertStringNotContainsString(self::KEY, print_r($connection, true));
    }

    public function testCannotBeSerialised(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('An assistant connection holds an API key and must not be serialised.');

        serialize(new AssistantConnection(self::KEY));
    }

    public function testRefusesAnEmptyKey(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The assistant API key must not be empty.');

        new AssistantConnection(' ');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidBaseUrls(): iterable
    {
        yield 'relative' => ['/v1'];
        yield 'other scheme' => ['ftp://ai.example.org'];
        yield 'credentials' => ['https://user:pass@ai.example.org'];
        yield 'space' => ['https://ai.example.org/v 1'];
        yield 'empty' => [''];
    }

    #[DataProvider('invalidBaseUrls')]
    public function testRefusesInvalidBaseUrls(string $url): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Assistant base URL is not valid');

        new AssistantConnection(self::KEY, $url);
    }
}

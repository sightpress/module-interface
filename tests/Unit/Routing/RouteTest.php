<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Routing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Routing\Route;
use SightPress\Module\Tests\Fake\Module\EventListHandler;
use SightPress\Module\Tests\Fake\Module\RequireLoginMiddleware;

#[CoversClass(Route::class)]
#[UsesClass(Guard::class)]
final class RouteTest extends TestCase
{
    public function testDefaults(): void
    {
        $route = new Route('events.index', '/events', EventListHandler::class);

        self::assertSame('events.index', $route->name);
        self::assertSame('/events', $route->path);
        self::assertSame(EventListHandler::class, $route->handler);
        self::assertSame(['GET'], $route->methods);
        self::assertSame([], $route->middleware);
    }

    public function testAllFields(): void
    {
        $route = new Route(
            'events.edit',
            '/admin/events/{id}',
            EventListHandler::class,
            ['GET', 'POST'],
            [RequireLoginMiddleware::class],
        );

        self::assertSame(['GET', 'POST'], $route->methods);
        self::assertSame([RequireLoginMiddleware::class], $route->middleware);
    }

    /**
     * @return iterable<string, array{string, string, string, array<mixed>, array<mixed>, string}>
     */
    public static function invalidRoutes(): iterable
    {
        $handler = EventListHandler::class;

        yield 'bad name' => ['Events', '/e', $handler, ['GET'], [], 'Route name is not valid ("Events")'];
        yield 'relative path' => ['e', 'events', $handler, ['GET'], [], 'Path of route "e" is not valid ("events")'];
        yield 'space in path' => ['e', '/my events', $handler, ['GET'], [], 'Path of route "e" is not valid ("/my events")'];
        yield 'empty handler' => ['e', '/e', ' ', ['GET'], [], 'Handler of route "e" must not be empty.'];
        yield 'no methods' => ['e', '/e', $handler, [], [], 'Route "e" must allow at least one HTTP method.'];
        yield 'lower-case method' => ['e', '/e', $handler, ['get'], [], 'HTTP method of route "e" is not valid ("get")'];
        yield 'method not a string' => ['e', '/e', $handler, [1], [], 'Methods of route "e"[0] must be a string.'];
        yield 'duplicate method' => ['e', '/e', $handler, ['GET', 'GET'], [], 'Methods of route "e" has duplicates'];
        yield 'empty middleware' => ['e', '/e', $handler, ['GET'], [''], 'Middleware of route "e" must not be empty.'];
        yield 'middleware map' => ['e', '/e', $handler, ['GET'], ['a' => 'x'], 'Middleware of route "e" must be a list.'];
    }

    /**
     * @param array<mixed> $methods
     * @param array<mixed> $middleware
     */
    #[DataProvider('invalidRoutes')]
    public function testRefusesInvalidRoutes(
        string $name,
        string $path,
        string $handler,
        array $methods,
        array $middleware,
        string $message,
    ): void {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        /** @phpstan-ignore argument.type, argument.type, argument.type */
        new Route($name, $path, $handler, $methods, $middleware);
    }
}

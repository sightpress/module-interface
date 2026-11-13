<?php

declare(strict_types=1);

namespace SightPress\Module\Routing;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One HTTP route: a path, the methods it answers and the PSR-15 handler that answers it.
 *
 * Handlers must return complete, accessible pages (WCAG 2.2 AA) and must check permissions themselves or through
 * middleware; a route has no access rule of its own.
 */
final readonly class Route
{
    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $path;

    /** @var class-string<RequestHandlerInterface> */
    public string $handler;

    /** @var non-empty-list<non-empty-string> */
    public array $methods;

    /** @var list<class-string<MiddlewareInterface>> */
    public array $middleware;

    /**
     * @param string                                  $name       unique route name, used to build URLs, for
     *                                                            example `events.show`
     * @param string                                  $path       starts with `/`; placeholders are written
     *                                                            `{name}`, for example `/events/{slug}`
     * @param class-string<RequestHandlerInterface>   $handler    built from the site's container
     * @param list<string>                            $methods    upper-case HTTP methods, `GET` by default
     * @param list<class-string<MiddlewareInterface>> $middleware run in the given order before the handler
     */
    public function __construct(
        string $name,
        string $path,
        string $handler,
        array $methods = ['GET'],
        array $middleware = [],
    ) {
        $this->name = Guard::matches(
            $name,
            Guard::DOTTED_NAME,
            'Route name',
            'use lower-case words joined by dots, for example "events.show"',
        );
        $this->path = Guard::matches(
            $path,
            '/^\/\S*$/',
            sprintf('Path of route "%s"', $name),
            'start with "/" and contain no spaces',
        );
        /** @var class-string<RequestHandlerInterface> $handler */
        $handler = Guard::notBlank($handler, sprintf('Handler of route "%s"', $name));
        $this->handler = $handler;

        $methods = Guard::listOfStrings($methods, sprintf('Methods of route "%s"', $name));
        if ($methods === []) {
            throw new InvalidValueException(sprintf('Route "%s" must allow at least one HTTP method.', $name));
        }
        $checked = [];
        foreach ($methods as $method) {
            $checked[] = Guard::matches(
                $method,
                '/^[A-Z]+$/',
                sprintf('HTTP method of route "%s"', $name),
                'use an upper-case method such as GET or POST',
            );
        }
        Guard::unique($checked, sprintf('Methods of route "%s"', $name));
        $this->methods = $checked;

        /** @var list<class-string<MiddlewareInterface>> $middleware */
        $middleware = Guard::listOfStrings($middleware, sprintf('Middleware of route "%s"', $name));
        foreach ($middleware as $class) {
            Guard::notBlank($class, sprintf('Middleware of route "%s"', $name));
        }
        $this->middleware = $middleware;
    }
}

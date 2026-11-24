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
 * Handlers must return complete, accessible pages (WCAG 2.2 AA). Give a route a `permission` and core checks it
 * before the handler runs (since 0.5); without one, the handler checks access itself
 * ({@see \SightPress\Module\Access\Viewer}).
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

    /** @var non-empty-string|null */
    public ?string $permission;

    public bool $critical;

    /**
     * @param string                                  $name       unique route name, used to build URLs, for
     *                                                            example `events.show`
     * @param string                                  $path       starts with `/`; placeholders are written
     *                                                            `{name}`, for example `/events/{slug}`
     * @param class-string<RequestHandlerInterface>   $handler    built from the site's container
     * @param list<string>                            $methods    upper-case HTTP methods, `GET` by default
     * @param list<class-string<MiddlewareInterface>> $middleware run in the given order before the handler
     * @param string|null                             $permission needed to use the route (since 0.5): a signed-out
     *                                                            visitor is sent to the sign-in page and back, and
     *                                                            anyone else without it gets core's "no access" page.
     *                                                            Paths under `/admin` also need `admin.access`, as
     *                                                            core's own admin screens do
     * @param bool                                    $critical   the route makes an important change (deletes data,
     *                                                            changes access or security), so sites with two-step
     *                                                            confirmation ask for a code before it runs (since
     *                                                            0.5). Needs a method that changes something (`POST`,
     *                                                            `PUT`, `PATCH` or `DELETE`); `GET` requests are never
     *                                                            held up
     */
    public function __construct(
        string $name,
        string $path,
        string $handler,
        array $methods = ['GET'],
        array $middleware = [],
        ?string $permission = null,
        bool $critical = false,
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

        $this->permission = $permission === null ? null : Guard::matches(
            $permission,
            Guard::DOTTED_NAME,
            sprintf('Permission of route "%s"', $name),
            'use a permission name such as "events.manage"',
        );
        if ($critical && array_intersect($checked, ['POST', 'PUT', 'PATCH', 'DELETE']) === []) {
            throw new InvalidValueException(sprintf(
                'Route "%s" is marked critical but only answers %s: a critical route must change something (POST, PUT, PATCH or DELETE).',
                $name,
                implode(', ', $checked),
            ));
        }
        $this->critical = $critical;
    }
}

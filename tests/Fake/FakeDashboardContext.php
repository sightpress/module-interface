<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use DateTimeZone;
use SightPress\Module\Dashboard\DashboardContext;

final readonly class FakeDashboardContext implements DashboardContext
{
    /**
     * @param non-empty-string $locale
     * @param list<string>     $permissions
     */
    public function __construct(
        private string $locale = 'en-GB',
        private string $timeZone = 'Europe/London',
        private array $permissions = [],
    ) {
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function timeZone(): DateTimeZone
    {
        return new DateTimeZone($this->timeZone);
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function url(string $route, array $parameters = []): string
    {
        return '/' . str_replace('.', '/', $route) . ($parameters === [] ? '' : '?' . http_build_query($parameters));
    }
}

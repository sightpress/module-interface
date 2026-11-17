<?php

declare(strict_types=1);

namespace SightPress\Module\Dashboard;

use DateTimeZone;

/**
 * What a dashboard widget may need to know about the person looking at the dashboard.
 *
 * Implemented by core only; widgets use it but must not implement it, so methods can be added in a minor release.
 */
interface DashboardContext
{
    /**
     * The person's admin locale, as a BCP 47 tag such as `en-GB`, for numbers, dates and text.
     *
     * @return non-empty-string
     */
    public function locale(): string;

    /**
     * The person's time zone; dates are stored in UTC and shown in this zone.
     */
    public function timeZone(): DateTimeZone;

    /**
     * Whether the person holds a permission on this site, for example `analytics.manage`.
     */
    public function can(string $permission): bool;

    /**
     * Site-relative URL of a named route, for example `admin.analytics` → `/admin/analytics`.
     *
     * @param non-empty-string      $route
     * @param array<string, string> $parameters values for the route's placeholders
     *
     * @return non-empty-string
     */
    public function url(string $route, array $parameters = []): string;
}

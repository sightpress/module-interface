<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Schedule\ScheduledJob;

/**
 * Adds background work that `schedule:run` does for each site where the module is on (since 0.5).
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface ScheduleRegistrar
{
    /**
     * @param class-string<ScheduledJob> $job core builds it from the site's container; jobs run in the order the
     *                                        modules registered them
     */
    public function add(string $job): void;
}

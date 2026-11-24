<?php

declare(strict_types=1);

namespace SightPress\Module\Schedule;

/**
 * Work a module does in the background, on every run of `schedule:run` (the operator runs it every minute, from
 * cron or the scheduler container), once per site where the module is on. Registered with
 * {@see \SightPress\Module\Registrar\ScheduleRegistrar::add()} and built from the site's container.
 *
 * A job decides itself whether anything is due (compare {@see ScheduleRun::$now} with what it stored), must finish
 * quickly and must be safe to run again after a crash: never send anything twice. Implemented by modules.
 */
interface ScheduledJob
{
    public function run(ScheduleRun $run): void;
}

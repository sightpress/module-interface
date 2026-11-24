<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\ScheduleRegistrar;
use SightPress\Module\Schedule\ScheduledJob;

final class InMemoryScheduleRegistrar implements ScheduleRegistrar
{
    /** @var list<class-string<ScheduledJob>> */
    public array $jobs = [];

    public function add(string $job): void
    {
        if (in_array($job, $this->jobs, true)) {
            throw DuplicateRegistration::of('Scheduled job', $job);
        }
        $this->jobs[] = $job;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Schedule\ScheduledJob;
use SightPress\Module\Schedule\ScheduleRun;

/**
 * Sends reminders for events starting in the next day, at most once per event, as a module author would.
 */
final class EventReminderJob implements ScheduledJob
{
    private const array EVENTS = ['fado' => '2026-12-01T20:00:00Z', 'workshop' => '2026-12-02T10:00:00Z'];

    /** @var array<string, true> events already reminded about */
    public array $sent = [];

    public function run(ScheduleRun $run): void
    {
        $count = 0;
        foreach (self::EVENTS as $event => $startsAt) {
            $due = new \DateTimeImmutable($startsAt)->modify('-1 day') <= $run->now;
            if ($due && !isset($this->sent[$event])) {
                $this->sent[$event] = true;
                ++$count;
            }
        }
        if ($count > 0) {
            $run->report(sprintf('Sent %d event %s.', $count, $count === 1 ? 'reminder' : 'reminders'));
        }
    }
}

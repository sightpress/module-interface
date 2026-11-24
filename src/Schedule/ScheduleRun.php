<?php

declare(strict_types=1);

namespace SightPress\Module\Schedule;

use DateTimeImmutable;
use SightPress\Module\Internal\Guard;

/**
 * One run of `schedule:run` for one site, passed to every {@see ScheduledJob}. Jobs report what they did in plain
 * lines, which the command prints.
 */
final class ScheduleRun
{
    public readonly DateTimeImmutable $now;

    /** @var list<string> */
    private array $lines = [];

    /**
     * @param DateTimeImmutable $now the moment of this run (stored in UTC); use it instead of the clock, so every
     *                               job of the run agrees on the time
     */
    public function __construct(DateTimeImmutable $now)
    {
        $this->now = Guard::utc($now);
    }

    /**
     * Adds a line to the command's output, for example `Sent 3 event reminders.`.
     */
    public function report(string $line): void
    {
        $this->lines[] = Guard::notBlank($line, 'Schedule report line');
    }

    /**
     * @return list<string>
     */
    public function lines(): array
    {
        return $this->lines;
    }
}

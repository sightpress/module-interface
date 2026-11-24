<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Schedule;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Schedule\ScheduleRun;

#[CoversClass(ScheduleRun::class)]
#[UsesClass(Guard::class)]
final class ScheduleRunTest extends TestCase
{
    public function testRunCollectsReportLines(): void
    {
        $run = new ScheduleRun(new DateTimeImmutable('2026-11-30 21:00', new \DateTimeZone('Europe/Lisbon')));

        self::assertSame('UTC', $run->now->getTimezone()->getName());
        self::assertSame([], $run->lines());
        $run->report('Sent 1 event reminder.');
        $run->report('Archived 2 past events.');
        self::assertSame(['Sent 1 event reminder.', 'Archived 2 past events.'], $run->lines());

        $this->expectException(InvalidValueException::class);
        $run->report(' ');
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Audit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Audit\AuditRecord;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(AuditRecord::class)]
#[UsesClass(Guard::class)]
final class AuditRecordTest extends TestCase
{
    public function testRecord(): void
    {
        $record = new AuditRecord('acme.events.event_deleted', 'Deleted the event “Fado night”.', 2, 'event', 'fado', ['tickets' => 3, 'tags' => ['music'], 'note' => null, 'free' => false]);

        self::assertSame('acme.events.event_deleted', $record->action);
        self::assertSame('Deleted the event “Fado night”.', $record->summary);
        self::assertSame(2, $record->actorId);
        self::assertSame('event', $record->subjectType);
        self::assertSame('fado', $record->subjectId);
        self::assertSame(['tickets' => 3, 'tags' => ['music'], 'note' => null, 'free' => false], $record->details);

        $minimal = new AuditRecord('acme.events.reminders_sent', 'Sent 3 reminders.');
        self::assertNull($minimal->actorId);
        self::assertNull($minimal->subjectType);
        self::assertNull($minimal->subjectId);
        self::assertSame([], $minimal->details);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalid(): iterable
    {
        yield 'action' => [['action' => 'Event deleted'], 'Audit action is not valid'];
        yield 'summary' => [['summary' => ''], 'Audit summary must not be empty.'];
        yield 'subject type' => [['subjectType' => 'Event'], 'Audit subject type is not valid'];
        yield 'details list' => [['details' => ['x']], 'must map names to text, numbers'];
        yield 'nested details' => [['details' => ['tags' => [['a']]]], 'must map names to text, numbers'];
        yield 'object detail' => [['details' => ['at' => new \DateTimeImmutable()]], 'must map names to text, numbers'];
        yield 'keyed list detail' => [['details' => ['tags' => ['a' => 'b']]], 'must map names to text, numbers'];
    }

    /**
     * @param array<string, mixed> $fields
     */
    #[DataProvider('invalid')]
    public function testInvalidValuesAreRefused(array $fields, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        /** @phpstan-ignore argument.type */
        new AuditRecord(...['action' => 'acme.events.saved', 'summary' => 'Saved.', ...$fields]);
    }
}

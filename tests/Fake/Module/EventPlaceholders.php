<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use DateTimeImmutable;
use SightPress\Module\Placeholder\PlaceholderContext;
use SightPress\Module\Placeholder\PlaceholderResolver;

/**
 * The values of the fake module's placeholders: the next event's date, and how many events the reader booked
 * (personal, so it only has a value for a signed-in member).
 */
final readonly class EventPlaceholders implements PlaceholderResolver
{
    public function __construct(private EventBookings $bookings)
    {
    }

    public function resolve(string $name, PlaceholderContext $context): string|int|float|DateTimeImmutable|null
    {
        return match ($name) {
            'acme_events.next_date' => new DateTimeImmutable('2026-12-04 19:30', new \DateTimeZone('UTC')),
            'acme_events.venue' => 'Casa do Fado',
            'acme_events.rating' => 4.5,
            'acme_events.my_bookings' => $context->viewerId() === null ? null : count(array_filter(
                $this->bookings->rows,
                static fn (array $row): bool => $row['user_id'] === $context->viewerId(),
            )),
            default => null,
        };
    }
}

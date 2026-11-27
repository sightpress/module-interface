<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

/**
 * The fake module's own table of bookings, in memory: who booked which event, with the email they gave and a ticket
 * file. Stands in for the module's database table and storage.
 */
final class EventBookings
{
    /** @var array<int, array{user_id: int, email: string, event: string, booked_at: string, ticket: string|null}> */
    public array $rows = [];

    /** @var array<string, string> ticket file name → contents */
    public array $files = [];

    public function book(int $userId, string $email, string $event, string $bookedAt, ?string $ticket = null): void
    {
        $this->rows[] = ['user_id' => $userId, 'email' => $email, 'event' => $event, 'booked_at' => $bookedAt, 'ticket' => $ticket];
        if ($ticket !== null) {
            $this->files[$ticket] = 'PDF';
        }
    }
}

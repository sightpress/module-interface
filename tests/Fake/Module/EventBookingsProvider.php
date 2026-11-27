<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Privacy\ErasureResult;
use SightPress\Module\Privacy\LawfulBasis;
use SightPress\Module\Privacy\PersonalDataCategory;
use SightPress\Module\Privacy\PersonalDataProvider;
use SightPress\Module\Privacy\PersonalDataSet;
use SightPress\Module\Privacy\Retention;
use SightPress\Module\Privacy\UserRef;
use SightPress\Module\Text\Message;

/**
 * Exports and erases a person's event bookings, found by account id or by the email they booked with.
 */
final readonly class EventBookingsProvider implements PersonalDataProvider
{
    public function __construct(private EventBookings $bookings)
    {
    }

    public function describe(): PersonalDataSet
    {
        return new PersonalDataSet(
            'acme_events.bookings',
            new Message('Event bookings', domain: 'acme_events'),
            new Message('To keep your place at the events you booked and send you your ticket.', domain: 'acme_events'),
            [PersonalDataCategory::Contact, PersonalDataCategory::Usage],
            LawfulBasis::Contract,
            Retention::days(365, 'acme_events.booking_retention_days'),
        );
    }

    public function export(UserRef $user): iterable
    {
        foreach ($this->bookings->rows as $row) {
            if ($this->belongsTo($row, $user)) {
                yield ['event' => $row['event'], 'email' => $row['email'], 'booked_at' => $row['booked_at']];
            }
        }
    }

    public function erase(UserRef $user): ErasureResult
    {
        $deleted = 0;
        $files = 0;
        foreach ($this->bookings->rows as $index => $row) {
            if (!$this->belongsTo($row, $user)) {
                continue;
            }
            if ($row['ticket'] !== null && isset($this->bookings->files[$row['ticket']])) {
                unset($this->bookings->files[$row['ticket']]);
                ++$files;
            }
            unset($this->bookings->rows[$index]);
            ++$deleted;
        }

        return new ErasureResult(deleted: $deleted, files: $files);
    }

    /**
     * @param array{user_id: int, email: string} $row
     */
    private function belongsTo(array $row, UserRef $user): bool
    {
        return $row['user_id'] === $user->userId || strcasecmp($row['email'], $user->email) === 0;
    }
}

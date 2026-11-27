<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Privacy\PersonalData;
use SightPress\Module\Privacy\PersonalDataCategory;

/**
 * One booking as the module's code sees it. The fields with personal data name the data set that exports and
 * erases them, so core's check can find a field nobody covers.
 */
final readonly class Booking
{
    public function __construct(
        public string $event,
        #[PersonalData('acme_events.bookings', PersonalDataCategory::Contact)] public string $email,
        #[PersonalData('acme_events.bookings')] public int $userId,
    ) {
    }
}

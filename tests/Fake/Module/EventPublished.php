<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

final readonly class EventPublished
{
    public function __construct(public string $eventId)
    {
    }
}

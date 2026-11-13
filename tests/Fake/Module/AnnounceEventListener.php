<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

final class AnnounceEventListener
{
    /** @var list<string> */
    public array $announced = [];

    public function __invoke(EventPublished $event): void
    {
        $this->announced[] = $event->eventId;
    }
}

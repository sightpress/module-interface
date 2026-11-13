<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\SubscriberRegistrar;

final class InMemorySubscriberRegistrar implements SubscriberRegistrar
{
    /** @var list<array{event: class-string, listener: class-string, priority: int}> */
    public array $listeners = [];

    public function listen(string $event, string $listener, int $priority = 0): void
    {
        $this->listeners[] = ['event' => $event, 'listener' => $listener, 'priority' => $priority];
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

/**
 * Adds event listeners. Events are plain objects dispatched through a PSR-14 event dispatcher.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface SubscriberRegistrar
{
    /**
     * @param class-string     $event    class (or interface) of the events to listen to
     * @param class-string     $listener invokable class, built from the site's container, whose `__invoke()`
     *                                   takes the event
     * @param int              $priority higher runs earlier; listeners with the same priority run in registration
     *                                   order
     */
    public function listen(string $event, string $listener, int $priority = 0): void;
}

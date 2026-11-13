<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Access\Permission;
use SightPress\Module\Admin\AdminMenuItem;
use SightPress\Module\ModuleInterface;
use SightPress\Module\ModuleManifest;
use SightPress\Module\ModuleRegistrar;
use SightPress\Module\ModuleRequirement;
use SightPress\Module\Routing\Route;
use SightPress\Module\Setting\Setting;
use SightPress\Module\Setting\SettingType;

/**
 * A small third-party module that uses every sub-registrar, as a module author would.
 */
final class EventsModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'acme.events',
            version: '1.2.0',
            requires: [new ModuleRequirement('page', '^1.0'), new ModuleRequirement('rbac')],
        );
    }

    public function register(ModuleRegistrar $registrar): void
    {
        $registrar->services()->autowire(EventCatalogue::class, public: true);
        $registrar->services()->factory(
            'acme.events.clock',
            static fn (): \DateTimeImmutable => new \DateTimeImmutable('now', new \DateTimeZone('UTC')),
        );

        $registrar->routes()->add(new Route('events.index', '/events', EventListHandler::class));
        $registrar->routes()->add(new Route(
            'admin.events.edit',
            '/admin/events/{id}',
            EventListHandler::class,
            ['GET', 'POST'],
            [RequireLoginMiddleware::class],
        ));

        $registrar->blockTypes()->add(EventCardBlock::class);

        $registrar->permissions()->add(new Permission('events.manage', 'Manage events', defaultRoles: ['admin']));

        $registrar->migrations()->add(new EventsMigrations());

        $registrar->adminMenu()->add(new AdminMenuItem(
            'events',
            'Events',
            'admin.events.edit',
            permission: 'events.manage',
            position: 40,
        ));

        $registrar->views()->add('acme_events', __DIR__ . '/templates');

        $registrar->subscribers()->listen(EventPublished::class, AnnounceEventListener::class, 10);

        $registrar->commands()->add(ImportEventsCommand::class);

        $registrar->settings()->add(new Setting(
            'events.page_size',
            'Events per page',
            SettingType::Integer,
            20,
            'How many events the events page lists before it adds a "Next page" link.',
        ));
    }
}

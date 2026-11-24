<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Access\Permission;
use SightPress\Module\Admin\AdminMenuItem;
use SightPress\Module\Block\BlockIcon;
use SightPress\Module\Block\BlockVariant;
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
            enabledByDefault: true,
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
            permission: 'events.manage',
        ));
        $registrar->routes()->add(new Route(
            'admin.events.delete',
            '/admin/events/{id}/delete',
            EventAdminHandler::class,
            ['POST'],
            permission: 'events.manage',
            critical: true,
        ));

        $registrar->blockTypes()->add(EventCardBlock::class);
        $registrar->blockTypes()->addVariant('quote', new BlockVariant('ticket', 'Ticket'));
        $registrar->blockTypes()->addIcon(new BlockIcon('eventbrite', 'Eventbrite', __DIR__ . '/icons/eventbrite.svg', ['eventbrite.com', 'eventbrite.co.uk'], '#f05537'));

        $registrar->permissions()->add(new Permission('events.manage', 'Manage events', defaultRoles: ['admin']));
        $registrar->permissions()->add(new Permission(
            'events.suggest',
            'Suggest events',
            'Send an event for an editor to review.',
            ['admin', 'contributor'],
            allowedOnSignUp: true,
        ));

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

        $registrar->search()->addProvider(EventSearchProvider::class);

        $registrar->dashboard()->add(UpcomingEventsWidget::class);

        $registrar->assistant()->addTask(EventDescriptionTask::class);

        $registrar->seo()->addProvider(EventSeoProvider::class);
        $registrar->schedule()->add(EventReminderJob::class);
        $registrar->translations()->add(__DIR__ . '/translations');
    }
}

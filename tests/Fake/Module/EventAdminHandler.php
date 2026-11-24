<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SightPress\Module\Access\Viewer;
use SightPress\Module\Audit\AuditRecord;
use SightPress\Module\Audit\AuditTrail;
use SightPress\Module\Http\AdminScreen;
use SightPress\Module\Http\Screens;
use SightPress\Module\Setting\SettingValues;
use SightPress\Module\Site\SiteInfo;
use SightPress\Module\Text\Message;

/**
 * The fake module's admin screen, written only against this package: lists events, and on `POST` deletes one,
 * writes the audit log and goes back to the list with a notice.
 */
final readonly class EventAdminHandler implements RequestHandlerInterface
{
    public function __construct(
        private Screens $screens,
        private SettingValues $settings,
        private AuditTrail $audit,
        private SiteInfo $site,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $viewer = Viewer::fromRequest($request);
        if ($request->getMethod() === 'POST') {
            $this->audit->record(new AuditRecord(
                'acme.events.event_deleted',
                'Deleted the event “Fado night”.',
                $viewer->userId,
                'event',
                'fado',
            ));

            return $this->screens->redirect($request, '/admin/events', new Message('Deleted “{title}”.', ['title' => 'Fado night'], 'acme_events'));
        }

        return $this->screens->admin($request, new AdminScreen(
            new Message('Events', domain: 'acme_events'),
            '@acme_events/admin/list.html.twig',
            [
                'events' => ['fado' => 'Fado night'],
                'page_size' => $this->settings->get('events.page_size'),
                'can_delete' => $viewer->can('events.manage'),
                'site_name' => $this->site->name,
            ],
        ));
    }
}

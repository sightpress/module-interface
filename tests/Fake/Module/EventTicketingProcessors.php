<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Privacy\ProcessorDeclaration;
use SightPress\Module\Privacy\ProcessorSource;
use SightPress\Module\Setting\SettingValues;
use SightPress\Module\Text\Message;

/**
 * Declares the ticketing service only while the site has a key for it.
 */
final readonly class EventTicketingProcessors implements ProcessorSource
{
    public function __construct(private SettingValues $settings)
    {
    }

    public function processors(): iterable
    {
        if (in_array($this->settings->get('acme_events.ticketing_key'), [null, ''], true)) {
            return;
        }

        yield new ProcessorDeclaration(
            'acme_events.tickets',
            'Acme Tickets Ltd',
            new Message('Your name and email address, to issue your ticket.', domain: 'acme_events'),
            ['GB', 'US'],
            ['api.acme-tickets.example'],
            'https://acme-tickets.example/privacy',
            new Message('UK IDTA for transfers to the United States.', domain: 'acme_events'),
        );
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Module;

use SightPress\Module\Dashboard\DashboardContext;
use SightPress\Module\Dashboard\DashboardWidget;

final class UpcomingEventsWidget implements DashboardWidget
{
    public function id(): string
    {
        return 'events.upcoming';
    }

    public function title(): string
    {
        return 'Upcoming events';
    }

    public function permission(): string
    {
        return 'events.manage';
    }

    public function position(): int
    {
        return 30;
    }

    public function render(DashboardContext $context): string
    {
        return sprintf(
            '<p>%d events in the next 7 days. <a href="%s">See all events</a></p>',
            3,
            htmlspecialchars($context->url('admin.events.edit', ['id' => 'all']), ENT_QUOTES),
        );
    }
}

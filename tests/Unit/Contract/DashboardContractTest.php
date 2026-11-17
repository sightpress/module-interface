<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Tests\Fake\FakeDashboardContext;
use SightPress\Module\Tests\Fake\Module\UpcomingEventsWidget;

/**
 * Contract test: a widget renders its content from the context core gives it, links by route name and states
 * the permission core checks.
 */
#[CoversNothing]
final class DashboardContractTest extends TestCase
{
    public function testWidgetRendersWithTheCoreContext(): void
    {
        $widget = new UpcomingEventsWidget();
        $context = new FakeDashboardContext('en-GB', 'Europe/Lisbon', ['events.manage']);

        self::assertSame('events.upcoming', $widget->id());
        self::assertSame('Upcoming events', $widget->title());
        self::assertSame(30, $widget->position());
        self::assertTrue($context->can($widget->permission()));
        self::assertSame('Europe/Lisbon', $context->timeZone()->getName());
        self::assertSame('en-GB', $context->locale());
        self::assertSame(
            '<p>3 events in the next 7 days. <a href="/admin/events/edit?id=all">See all events</a></p>',
            $widget->render($context),
        );
        self::assertSame('/admin/events', $context->url('admin.events'));
    }
}

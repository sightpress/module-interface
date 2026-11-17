<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Dashboard\DashboardWidget;
use SightPress\Module\Registrar\DashboardRegistrar;

final class InMemoryDashboardRegistrar implements DashboardRegistrar
{
    /** @var list<class-string<DashboardWidget>> */
    public array $widgets = [];

    public function add(string $widget): void
    {
        if (in_array($widget, $this->widgets, true)) {
            throw DuplicateRegistration::of('Dashboard widget', $widget);
        }
        $this->widgets[] = $widget;
    }
}

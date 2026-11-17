<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Dashboard\DashboardWidget;

/**
 * Adds widgets to the admin dashboard.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface DashboardRegistrar
{
    /**
     * @param class-string<DashboardWidget> $widget core builds it from the site's container
     */
    public function add(string $widget): void;
}

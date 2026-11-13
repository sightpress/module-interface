<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Setting\Setting;

/**
 * Adds site settings. Core stores the values per site, shows them on the module's settings screen and records
 * every change in the audit log.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface SettingRegistrar
{
    public function add(Setting $setting): void;
}

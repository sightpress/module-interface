<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\SettingRegistrar;
use SightPress\Module\Setting\Setting;

final class InMemorySettingRegistrar implements SettingRegistrar
{
    /** @var array<string, Setting> */
    public array $settings = [];

    public function add(Setting $setting): void
    {
        if (isset($this->settings[$setting->key])) {
            throw DuplicateRegistration::of('Setting', $setting->key);
        }
        $this->settings[$setting->key] = $setting;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Setting;

/**
 * Reads the site's settings, typed as their {@see SettingType} says, with the registered default when none was
 * saved. In the site's container as `SettingValues::class`. Settings are changed on the admin's settings screen,
 * which dispatches {@see \SightPress\Module\Event\SettingsChanged}.
 *
 * Implemented by core only, so methods can be added in a minor release.
 */
interface SettingValues
{
    /**
     * @param string $key a registered setting, for example `events.page_size`
     *
     * @return string|int|bool|null `int` for {@see SettingType::Integer}, `bool` for {@see SettingType::Boolean},
     *                              a string for the other types, null when it has neither a value nor a default
     *
     * @throws \SightPress\Module\Exception\InvalidValueException for a key no module registered
     */
    public function get(string $key): string|int|bool|null;
}

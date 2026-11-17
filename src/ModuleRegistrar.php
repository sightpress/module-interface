<?php

declare(strict_types=1);

namespace SightPress\Module;

use SightPress\Module\Registrar\AdminMenuRegistrar;
use SightPress\Module\Registrar\AssistantRegistrar;
use SightPress\Module\Registrar\BlockTypeRegistrar;
use SightPress\Module\Registrar\CommandRegistrar;
use SightPress\Module\Registrar\DashboardRegistrar;
use SightPress\Module\Registrar\MigrationRegistrar;
use SightPress\Module\Registrar\PermissionRegistrar;
use SightPress\Module\Registrar\RouteRegistrar;
use SightPress\Module\Registrar\SearchRegistrar;
use SightPress\Module\Registrar\ServiceRegistrar;
use SightPress\Module\Registrar\SettingRegistrar;
use SightPress\Module\Registrar\SubscriberRegistrar;
use SightPress\Module\Registrar\ViewRegistrar;

/**
 * Passed to {@see ModuleInterface::register()}: one typed sub-registrar per kind of thing a module can add.
 *
 * Implemented by core only. Modules call it but must not implement it, so new sub-registrars can be added in a
 * minor release. Everything registered is tied to the calling module, so core can name the module in errors and
 * leave its contributions out on sites where it is off.
 */
interface ModuleRegistrar
{
    public function services(): ServiceRegistrar;

    public function routes(): RouteRegistrar;

    public function blockTypes(): BlockTypeRegistrar;

    public function permissions(): PermissionRegistrar;

    public function migrations(): MigrationRegistrar;

    public function adminMenu(): AdminMenuRegistrar;

    public function views(): ViewRegistrar;

    public function subscribers(): SubscriberRegistrar;

    public function commands(): CommandRegistrar;

    public function settings(): SettingRegistrar;

    /** Site search: document providers and an optional back-end (since 0.4). */
    public function search(): SearchRegistrar;

    /** Admin dashboard widgets (since 0.4). */
    public function dashboard(): DashboardRegistrar;

    /** AI assistant providers and tasks (since 0.4). */
    public function assistant(): AssistantRegistrar;
}

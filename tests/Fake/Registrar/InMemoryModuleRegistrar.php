<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\ModuleRegistrar;

/**
 * Records everything a module registers, so tests can check it without booting core.
 */
final readonly class InMemoryModuleRegistrar implements ModuleRegistrar
{
    public InMemoryServiceRegistrar $services;
    public InMemoryRouteRegistrar $routes;
    public InMemoryBlockTypeRegistrar $blockTypes;
    public InMemoryPermissionRegistrar $permissions;
    public InMemoryMigrationRegistrar $migrations;
    public InMemoryAdminMenuRegistrar $adminMenu;
    public InMemoryViewRegistrar $views;
    public InMemorySubscriberRegistrar $subscribers;
    public InMemoryCommandRegistrar $commands;
    public InMemorySettingRegistrar $settings;

    public function __construct()
    {
        $this->services = new InMemoryServiceRegistrar();
        $this->routes = new InMemoryRouteRegistrar();
        $this->blockTypes = new InMemoryBlockTypeRegistrar();
        $this->permissions = new InMemoryPermissionRegistrar();
        $this->migrations = new InMemoryMigrationRegistrar();
        $this->adminMenu = new InMemoryAdminMenuRegistrar();
        $this->views = new InMemoryViewRegistrar();
        $this->subscribers = new InMemorySubscriberRegistrar();
        $this->commands = new InMemoryCommandRegistrar();
        $this->settings = new InMemorySettingRegistrar();
    }

    public function services(): InMemoryServiceRegistrar
    {
        return $this->services;
    }

    public function routes(): InMemoryRouteRegistrar
    {
        return $this->routes;
    }

    public function blockTypes(): InMemoryBlockTypeRegistrar
    {
        return $this->blockTypes;
    }

    public function permissions(): InMemoryPermissionRegistrar
    {
        return $this->permissions;
    }

    public function migrations(): InMemoryMigrationRegistrar
    {
        return $this->migrations;
    }

    public function adminMenu(): InMemoryAdminMenuRegistrar
    {
        return $this->adminMenu;
    }

    public function views(): InMemoryViewRegistrar
    {
        return $this->views;
    }

    public function subscribers(): InMemorySubscriberRegistrar
    {
        return $this->subscribers;
    }

    public function commands(): InMemoryCommandRegistrar
    {
        return $this->commands;
    }

    public function settings(): InMemorySettingRegistrar
    {
        return $this->settings;
    }
}

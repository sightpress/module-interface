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
    public InMemorySearchRegistrar $search;
    public InMemoryDashboardRegistrar $dashboard;
    public InMemoryAssistantRegistrar $assistant;
    public InMemorySeoRegistrar $seo;
    public InMemorySmsRegistrar $sms;
    public InMemoryScheduleRegistrar $schedule;
    public InMemoryTranslationRegistrar $translations;
    public InMemoryPrivacyRegistrar $privacy;
    public InMemoryPlaceholderRegistrar $placeholders;

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
        $this->search = new InMemorySearchRegistrar();
        $this->dashboard = new InMemoryDashboardRegistrar();
        $this->assistant = new InMemoryAssistantRegistrar();
        $this->seo = new InMemorySeoRegistrar();
        $this->sms = new InMemorySmsRegistrar();
        $this->schedule = new InMemoryScheduleRegistrar();
        $this->translations = new InMemoryTranslationRegistrar();
        $this->privacy = new InMemoryPrivacyRegistrar();
        $this->placeholders = new InMemoryPlaceholderRegistrar();
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

    public function search(): InMemorySearchRegistrar
    {
        return $this->search;
    }

    public function dashboard(): InMemoryDashboardRegistrar
    {
        return $this->dashboard;
    }

    public function assistant(): InMemoryAssistantRegistrar
    {
        return $this->assistant;
    }

    public function seo(): InMemorySeoRegistrar
    {
        return $this->seo;
    }

    public function sms(): InMemorySmsRegistrar
    {
        return $this->sms;
    }

    public function schedule(): InMemoryScheduleRegistrar
    {
        return $this->schedule;
    }

    public function translations(): InMemoryTranslationRegistrar
    {
        return $this->translations;
    }

    public function privacy(): InMemoryPrivacyRegistrar
    {
        return $this->privacy;
    }

    public function placeholders(): InMemoryPlaceholderRegistrar
    {
        return $this->placeholders;
    }
}

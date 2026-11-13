<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Block\A11ySeverity;
use SightPress\Module\Console\Command;
use SightPress\Module\Setting\SettingType;
use SightPress\Module\Tests\Fake\ArrayCommandInput;
use SightPress\Module\Tests\Fake\BufferedCommandOutput;
use SightPress\Module\Tests\Fake\FakeBlockContext;
use SightPress\Module\Tests\Fake\Module\AnnounceEventListener;
use SightPress\Module\Tests\Fake\Module\EventCardBlock;
use SightPress\Module\Tests\Fake\Module\EventCatalogue;
use SightPress\Module\Tests\Fake\Module\EventListHandler;
use SightPress\Module\Tests\Fake\Module\EventPublished;
use SightPress\Module\Tests\Fake\Module\EventsModule;
use SightPress\Module\Tests\Fake\Module\ImportEventsCommand;
use SightPress\Module\Tests\Fake\Module\RequireLoginMiddleware;
use SightPress\Module\Tests\Fake\Registrar\DuplicateRegistration;
use SightPress\Module\Tests\Fake\Registrar\InMemoryModuleRegistrar;

/**
 * Contract test: a module written only against this package registers everything through the registrar
 * interfaces, and nothing in the contract needs a framework type.
 */
#[CoversNothing]
final class ModuleRegistrationTest extends TestCase
{
    public function testManifestDescribesTheModule(): void
    {
        $manifest = new EventsModule()->manifest();

        self::assertSame('acme.events', $manifest->id);
        self::assertSame('1.2.0', $manifest->version);
        self::assertSame(['page', 'rbac'], array_map(static fn ($r): string => $r->moduleId, $manifest->requires));
        self::assertSame(['^1.0', '*'], array_map(static fn ($r): string => $r->constraint, $manifest->requires));
        self::assertFalse($manifest->required);
        self::assertFalse($manifest->isOnForNewSites());
    }

    public function testModuleRegistersThroughEverySubRegistrar(): void
    {
        $registrar = new InMemoryModuleRegistrar();
        $module = new EventsModule();

        $module->register($registrar);

        self::assertTrue($registrar->services->services[EventCatalogue::class]['public']);
        self::assertSame(EventCatalogue::class, $registrar->services->services[EventCatalogue::class]['class']);
        self::assertFalse($registrar->services->services['acme.events.clock']['public']);
        self::assertNotNull($registrar->services->services['acme.events.clock']['factory']);

        self::assertSame(['events.index', 'admin.events.edit'], array_keys($registrar->routes->routes));
        $edit = $registrar->routes->routes['admin.events.edit'];
        self::assertSame(EventListHandler::class, $edit->handler);
        self::assertSame(['GET', 'POST'], $edit->methods);
        self::assertSame([RequireLoginMiddleware::class], $edit->middleware);

        self::assertSame([EventCardBlock::class], $registrar->blockTypes->blockTypes);
        self::assertSame(['admin'], $registrar->permissions->permissions['events.manage']->defaultRoles);
        self::assertSame(['Acme\Events\Migrations'], array_keys($registrar->migrations->providers));
        self::assertStringEndsWith('/migrations', $registrar->migrations->providers['Acme\Events\Migrations']->directory());
        self::assertSame('events.manage', $registrar->adminMenu->items['events']->permission);
        self::assertSame(['acme_events'], array_keys($registrar->views->paths));
        self::assertSame(
            [['event' => EventPublished::class, 'listener' => AnnounceEventListener::class, 'priority' => 10]],
            $registrar->subscribers->listeners,
        );
        self::assertSame([ImportEventsCommand::class], $registrar->commands->commands);
        self::assertSame(SettingType::Integer, $registrar->settings->settings['events.page_size']->type);
        self::assertSame(20, $registrar->settings->settings['events.page_size']->default);
    }

    public function testEveryAdminMenuItemLinksToARegisteredRoute(): void
    {
        $registrar = new InMemoryModuleRegistrar();
        new EventsModule()->register($registrar);

        foreach ($registrar->adminMenu->items as $item) {
            self::assertArrayHasKey($item->route, $registrar->routes->routes, $item->id);
            if ($item->permission !== null) {
                self::assertArrayHasKey($item->permission, $registrar->permissions->permissions, $item->id);
            }
        }
    }

    public function testRegisteringTheSameModuleTwiceIsRefused(): void
    {
        $registrar = new InMemoryModuleRegistrar();
        new EventsModule()->register($registrar);

        $this->expectException(DuplicateRegistration::class);
        $this->expectExceptionMessage('Service "SightPress\Module\Tests\Fake\Module\EventCatalogue" is already registered.');

        new EventsModule()->register($registrar);
    }

    public function testBlockTypeRendersHtmlMarkdownAndStructuredData(): void
    {
        $block = new EventCardBlock();
        $context = new FakeBlockContext('pt-PT', 'https://example.org/pt');
        $data = ['title' => 'Fado & poetry <night>', 'slug' => 'fado', 'startsAt' => '2026-12-01T20:00:00Z'];

        self::assertSame(
            '<article><h2><a href="https://example.org/pt/events/fado">Fado &amp; poetry &lt;night&gt;</a></h2>'
            . '<p><time datetime="2026-12-01T20:00:00Z">2026-12-01T20:00:00Z</time></p></article>',
            $block->render($data, $context),
        );
        self::assertSame(
            "## [Fado & poetry <night>](https://example.org/pt/events/fado)\n\nStarts at 2026-12-01T20:00:00Z.\n",
            $block->renderMarkdown($data, $context),
        );
        self::assertSame('pt-PT', $block->structuredData($data, $context)['inLanguage'] ?? null);
        self::assertNull($block->structuredData(['title' => ''] + $data, $context));
        self::assertNull($context->mediaUrl('missing'));
    }

    public function testBlockTypeUpgradesOldDataAndChecksAccessibility(): void
    {
        $block = new EventCardBlock();

        self::assertSame(
            ['title' => '', 'startsAt' => '2026-12-01T20:00:00Z'],
            $block->upgradeData(1, ['title' => '', 'date' => '2026-12-01T20:00:00Z']),
        );

        [$rule] = $block->a11yRules();
        self::assertSame([], $rule->check(['title' => 'Fado night']));

        [$issue] = $rule->check(['title' => ' ']);
        self::assertSame('event_card.title_missing', $issue->rule);
        self::assertSame(A11ySeverity::Error, $issue->severity);
        self::assertSame('/title', $issue->field);
        self::assertSame(['2.4.4', '4.1.2'], $issue->wcag);
    }

    public function testCommandRunsAgainstCoreProvidedInputAndOutput(): void
    {
        $command = new ImportEventsCommand();
        $output = new BufferedCommandOutput();

        self::assertSame(['file', 'locale', 'dry-run'], array_map(static fn ($p): string => $p->name, $command->parameters()));

        self::assertSame(Command::INVALID, $command->run(new ArrayCommandInput(), $output));
        self::assertSame(['Give the path to an .ics file.'], $output->errors);

        self::assertSame(
            Command::SUCCESS,
            $command->run(new ArrayCommandInput(['file' => 'events.ics'], [], ['dry-run']), $output),
        );
        self::assertSame(['Would import 0 events from events.ics (locale en-GB).'], $output->lines);
    }

    public function testListenerReceivesTheEvent(): void
    {
        $listener = new AnnounceEventListener();
        $listener(new EventPublished('42'));

        self::assertSame(['42'], $listener->announced);
    }
}

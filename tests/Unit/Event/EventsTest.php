<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Event;

use Closure;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Event\AccountChanged;
use SightPress\Module\Event\AccountRegistered;
use SightPress\Module\Event\AnalyticsDataDeleted;
use SightPress\Module\Event\AssistantUsed;
use SightPress\Module\Event\LocaleRemoved;
use SightPress\Module\Event\MediaChanged;
use SightPress\Module\Event\MediaDeleted;
use SightPress\Module\Event\MediaUploaded;
use SightPress\Module\Event\MenuChanged;
use SightPress\Module\Event\PageCloned;
use SightPress\Module\Event\PageCreated;
use SightPress\Module\Event\PageMoved;
use SightPress\Module\Event\PagePurged;
use SightPress\Module\Event\PageSaved;
use SightPress\Module\Event\PageScheduled;
use SightPress\Module\Event\PageTransitioned;
use SightPress\Module\Event\RedirectChanged;
use SightPress\Module\Event\RevisionRestored;
use SightPress\Module\Event\RoleChanged;
use SightPress\Module\Event\ScheduledStepFailed;
use SightPress\Module\Event\SettingsChanged;
use SightPress\Module\Event\SiteModuleChanged;
use SightPress\Module\Event\ThemeApplied;
use SightPress\Module\Event\ThemeColoursChanged;
use SightPress\Module\Event\TranslationPublished;
use SightPress\Module\Event\UserDataExported;
use SightPress\Module\Event\UserErased;
use SightPress\Module\Event\UserErasureCancelled;
use SightPress\Module\Event\UserErasureScheduled;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversNamespace('SightPress\Module\Event')]
#[UsesClass(Guard::class)]
final class EventsTest extends TestCase
{
    public function testPrivacyEventsCarryIdsAndDatesOnly(): void
    {
        $exported = new UserDataExported(7, null);
        self::assertSame([7, null], [$exported->userId, $exported->requestedBy]);

        $scheduled = new UserErasureScheduled(7, 7, new DateTimeImmutable('2026-12-04 10:00', new \DateTimeZone('Europe/Lisbon')));
        self::assertSame([7, 7, '2026-12-04T10:00:00+00:00'], [$scheduled->userId, $scheduled->requestedBy, $scheduled->erasesAt->format(DATE_ATOM)]);

        $cancelled = new UserErasureCancelled(7, 7);
        self::assertSame([7, 7], [$cancelled->userId, $cancelled->cancelledBy]);

        $erased = new UserErased(7, 2);
        self::assertSame([7, 2], [$erased->userId, $erased->requestedBy]);

        foreach ([
            static fn (): object => new UserDataExported(-1, null),
            static fn (): object => new UserErasureScheduled(-1, null, new DateTimeImmutable()),
            static fn (): object => new UserErasureCancelled(-1, null),
            static fn (): object => new UserErased(-1, null),
        ] as $make) {
            try {
                $make();
                self::fail('No exception.');
            } catch (InvalidValueException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function testPageEventsCarryIdsAndScalars(): void
    {
        $created = new PageCreated(4, 'en-GB', 2);
        self::assertSame([4, 'en-GB', 2], [$created->pageId, $created->locale, $created->authorId]);

        $saved = new PageSaved(4, 'pt-PT', 3, null);
        self::assertSame([4, 'pt-PT', 3, null], [$saved->pageId, $saved->locale, $saved->revision, $saved->savedBy]);

        $moved = new PageMoved(4, null, 7, 0, 5, 2);
        self::assertSame([null, 7, 0, 5, 2], [$moved->fromParentId, $moved->toParentId, $moved->position, $moved->revision, $moved->movedBy]);

        $cloned = new PageCloned(9, 4, 2);
        self::assertSame([9, 4, 2], [$cloned->pageId, $cloned->sourceId, $cloned->clonedBy]);

        $purged = new PagePurged(4, 'About', [4, 5], null);
        self::assertSame(['About', [4, 5]], [$purged->title, $purged->pageIds]);

        $scheduled = new PageScheduled(4, new DateTimeImmutable('2026-12-01 09:00', new \DateTimeZone('Europe/Lisbon')), null, 2);
        self::assertSame('2026-12-01T09:00:00+00:00', $scheduled->publishAt?->format(DATE_ATOM));
        self::assertNull($scheduled->unpublishAt);
        $unscheduled = new PageScheduled(4, null, new DateTimeImmutable('2026-12-01 10:00+01:00'), 2);
        self::assertSame('2026-12-01T09:00:00+00:00', $unscheduled->unpublishAt?->format(DATE_ATOM));

        $failed = new ScheduledStepFailed(4, 'unpublish', 'The page is in the trash', 2);
        self::assertSame(['unpublish', 'The page is in the trash'], [$failed->transition, $failed->reason]);

        $restored = new RevisionRestored(4, 'en-GB', 2, 6, 1);
        self::assertSame([2, 6, 1], [$restored->restored, $restored->revision, $restored->restoredBy]);

        $translation = new TranslationPublished(4, 'he', true, 2, 1);
        self::assertTrue($translation->published);

        $redirect = new RedirectChanged(RedirectChanged::REMOVED, 'en-GB', 'old', null, 1);
        self::assertSame(['removed', 'old', null], [$redirect->change, $redirect->from, $redirect->to]);
    }

    public function testPageTransitionedSaysWhetherThePageWentOnOrOffTheSite(): void
    {
        $publish = new PageTransitioned(4, 'publish', 'approved', 'published', 2);
        self::assertTrue($publish->published());
        self::assertFalse($publish->unpublished());
        self::assertNull($publish->comment);

        $unpublish = new PageTransitioned(4, 'unpublish', 'published', 'draft', null, 'Out of date');
        self::assertFalse($unpublish->published());
        self::assertTrue($unpublish->unpublished());
        self::assertSame('Out of date', $unpublish->comment);

        $submit = new PageTransitioned(4, 'submit', 'draft', 'in_review', 2);
        self::assertFalse($submit->published() || $submit->unpublished());
        self::assertContains('trash', PageTransitioned::STATES);
        self::assertContains('untrash', PageTransitioned::TRANSITIONS);
    }

    public function testMediaMenuAccountAndSiteEvents(): void
    {
        $uploaded = new MediaUploaded(3, 'media/2026/11/a.jpg', 'image', 2);
        self::assertSame(['media/2026/11/a.jpg', 'image', 2], [$uploaded->key, $uploaded->kind, $uploaded->uploadedBy]);
        self::assertSame(['alt'], new MediaChanged(3, 2, ['alt'])->fields);
        self::assertSame('a.jpg', new MediaDeleted(3, 'a.jpg', null)->originalName);

        $menu = new MenuChanged('footer', MenuChanged::MOVED_UP, 8, null, 'https://example.org', 2);
        self::assertSame(['footer', 'moved_up', 8, null, 'https://example.org'], [$menu->menu, $menu->change, $menu->itemId, $menu->pageId, $menu->url]);

        $registered = new AccountRegistered(5, 'member', 'admin');
        self::assertSame(['member', 'admin'], [$registered->role, $registered->refusedRole]);
        self::assertNull(new AccountRegistered(5, 'member')->refusedRole);
        self::assertSame(['email'], new AccountChanged(5, 5, ['email'])->fields);
        $role = new RoleChanged(5, 1, null, 'editor');
        self::assertSame([null, 'editor', 1], [$role->from, $role->to, $role->changedBy]);

        $settings = new SettingsChanged(['site.name', 'events.page_size'], 1);
        self::assertTrue($settings->has('events.page_size'));
        self::assertFalse($settings->has('site.tagline'));
        self::assertSame(1, $settings->actorId);

        self::assertSame('pt-PT', new LocaleRemoved('pt-PT', 1)->locale);
        $module = new SiteModuleChanged('acme.events', 'Events', SiteModuleChanged::DATA_DELETED, null);
        self::assertSame(['acme.events', 'Events', 'data_deleted'], [$module->moduleId, $module->name, $module->change]);

        $theme = new ThemeApplied('sightpress/theme-default', 'Default', null, 1);
        self::assertSame(['sightpress/theme-default', 'Default', null], [$theme->themeId, $theme->name, $theme->previous]);
        self::assertSame(['light.primary'], new ThemeColoursChanged('sightpress/theme-default', 'Default', ['light.primary'], 1)->colours);

        self::assertNull(new AnalyticsDataDeleted(null)->actorId);
        $used = new AssistantUsed(2, 'alt_text', 'test', 'echo-1', 10, 4, 'ok');
        self::assertSame(['alt_text', 'test', 'echo-1', 10, 4, 'ok'], [$used->task, $used->provider, $used->model, $used->inputTokens, $used->outputTokens, $used->outcome]);
    }

    /**
     * @return iterable<string, array{Closure(): object, string}>
     */
    public static function invalidEvents(): iterable
    {
        yield 'negative page id' => [static fn (): object => new PageCreated(-1, 'en-GB', null), 'Page id must not be negative (-1).'];
        yield 'bad locale' => [static fn (): object => new PageSaved(1, 'english', 1, null), 'Page locale is not valid ("english")'];
        yield 'unknown step' => [static fn (): object => new PageTransitioned(1, 'delete', 'draft', 'trash', null), 'Page workflow step is not valid ("delete")'];
        yield 'unknown state' => [static fn (): object => new PageTransitioned(1, 'trash', 'gone', 'trash', null), 'Page state before the step is not valid ("gone")'];
        yield 'unknown target state' => [static fn (): object => new PageTransitioned(1, 'trash', 'draft', 'bin', null), 'Page state after the step is not valid ("bin")'];
        yield 'bad scheduled step' => [static fn (): object => new ScheduledStepFailed(1, 'archive', 'x', null), 'Scheduled step is not valid ("archive")'];
        /** @phpstan-ignore argument.type */
        yield 'bad page ids' => [static fn (): object => new PagePurged(1, 'A', [1, '2'], null), 'Deleted page ids[1] must be an id'];
        /** @phpstan-ignore argument.type */
        yield 'bad redirect change' => [static fn (): object => new RedirectChanged('changed', 'en-GB', 'a', 'b', null), 'Redirect change is not valid ("changed")'];
        yield 'bad media kind' => [static fn (): object => new MediaUploaded(1, 'k', 'pdf', null), 'Media kind is not valid ("pdf")'];
        yield 'bad menu name' => [static fn (): object => new MenuChanged('Main', MenuChanged::ADDED, 1, null, null, null), 'Menu name is not valid ("Main")'];
        yield 'bad menu change' => [static fn (): object => new MenuChanged('main', 'renamed', 1, null, null, null), 'Menu change is not valid ("renamed")'];
        yield 'blank role' => [static fn (): object => new RoleChanged(1, null, null, ' '), 'Role must not be empty.'];
        /** @phpstan-ignore argument.type */
        yield 'bad module change' => [static fn (): object => new SiteModuleChanged('acme.events', 'Events', 'removed', null), 'Module change is not valid ("removed")'];
        /** @phpstan-ignore argument.type */
        yield 'bad settings' => [static fn (): object => new SettingsChanged(['a' => 'site.name'], null), 'Changed settings must be a list.'];
        yield 'negative tokens' => [static fn (): object => new AssistantUsed(null, 't', 'p', 'm', -1, 0, 'ok'), 'Input tokens must not be negative (-1).'];
        yield 'blank theme' => [static fn (): object => new ThemeApplied('', 'Default', null, null), 'Theme id must not be empty.'];
    }

    /**
     * @param Closure(): object $create
     */
    #[DataProvider('invalidEvents')]
    public function testInvalidValuesAreRefusedWithAMessageNamingTheField(Closure $create, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        $create();
    }
}

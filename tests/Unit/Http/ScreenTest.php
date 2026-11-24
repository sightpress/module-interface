<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Http;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Http\AdminScreen;
use SightPress\Module\Http\PublicScreen;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Seo\SeoEntry;
use SightPress\Module\Text\Message;

#[CoversClass(AdminScreen::class)]
#[CoversClass(PublicScreen::class)]
#[UsesClass(Guard::class)]
#[UsesClass(Message::class)]
#[UsesClass(SeoEntry::class)]
final class ScreenTest extends TestCase
{
    public function testAdminScreen(): void
    {
        $title = new Message('Events', domain: 'acme_events');
        $screen = new AdminScreen($title, '@acme_events/admin/list.html.twig', ['events' => []], 422);

        self::assertSame($title, $screen->title);
        self::assertSame('@acme_events/admin/list.html.twig', $screen->template);
        self::assertSame(['events' => []], $screen->context);
        self::assertSame(422, $screen->status);
        self::assertSame(200, new AdminScreen('Events', '@acme_events/list.twig')->status);
    }

    public function testPublicScreen(): void
    {
        $seo = new SeoEntry('/events/fado', 'en-GB', 'Fado night', new DateTimeImmutable());
        $screen = new PublicScreen('Fado night', '@acme_events/event.html.twig', ['event' => 'fado'], seo: $seo);

        self::assertSame('Fado night', $screen->title);
        self::assertSame($seo, $screen->seo);
        self::assertFalse($screen->noindex);
        self::assertSame(200, $screen->status);

        $search = new PublicScreen('Search events', '@acme_events/search.html.twig', status: 404, noindex: true);
        self::assertTrue($search->noindex);
        self::assertNull($search->seo);
        self::assertSame([], $search->context);
    }

    /**
     * @return iterable<string, array{callable(): object, string}>
     */
    public static function invalid(): iterable
    {
        yield 'blank admin title' => [static fn (): object => new AdminScreen(' ', '@a/b.twig'), 'Screen title must not be empty.'];
        yield 'blank page title' => [static fn (): object => new PublicScreen('', '@a/b.twig'), 'Page title must not be empty.'];
        yield 'core template' => [static fn (): object => new AdminScreen('A', 'admin/layout.html.twig'), 'Template is not valid'];
        yield 'climbing template' => [static fn (): object => new AdminScreen('A', '@a/../core/x.twig'), 'Template is not valid'];
        yield 'variable name' => [static fn (): object => new PublicScreen('A', '@a/b.twig', ['bad name' => 1]), 'Template variable names must be words'];
        /** @phpstan-ignore argument.type */
        yield 'list context' => [static fn (): object => new PublicScreen('A', '@a/b.twig', [1]), 'Template variable names must be words'];
        yield 'status' => [static fn (): object => new AdminScreen('A', '@a/b.twig', status: 302), 'HTTP status must be 2xx, 4xx or 5xx (302)'];
    }

    /**
     * @param callable(): object $create
     */
    #[DataProvider('invalid')]
    public function testInvalidValuesAreRefused(callable $create, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        $create();
    }
}

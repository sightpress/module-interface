<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Admin\AdminMenuItem;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(AdminMenuItem::class)]
#[UsesClass(Guard::class)]
final class AdminMenuItemTest extends TestCase
{
    public function testDefaults(): void
    {
        $item = new AdminMenuItem('events', 'Events', 'admin.events.index');

        self::assertSame('events', $item->id);
        self::assertSame('Events', $item->label);
        self::assertSame('admin.events.index', $item->route);
        self::assertNull($item->permission);
        self::assertNull($item->parent);
        self::assertSame(0, $item->position);
    }

    public function testAllFields(): void
    {
        $item = new AdminMenuItem('events.categories', 'Event categories', 'admin.events.categories', 'events.manage', 'events', 5);

        self::assertSame('events.manage', $item->permission);
        self::assertSame('events', $item->parent);
        self::assertSame(5, $item->position);
    }

    /**
     * @return iterable<string, array{string, string, string, ?string, ?string, string}>
     */
    public static function invalidItems(): iterable
    {
        yield 'bad id' => ['Events', 'Events', 'events', null, null, 'Admin menu item id is not valid ("Events")'];
        yield 'empty label' => ['events', '', 'events', null, null, 'Label of admin menu item "events" must not be empty.'];
        yield 'bad route' => ['events', 'Events', '/events', null, null, 'Route of admin menu item "events"'];
        yield 'bad permission' => ['events', 'Events', 'events', 'manage', null, 'Permission of admin menu item "events"'];
        yield 'bad parent' => ['events', 'Events', 'events', null, 'Top', 'Parent of admin menu item "events"'];
        yield 'own parent' => ['events', 'Events', 'events', null, 'events', 'must not be its own parent.'];
    }

    #[DataProvider('invalidItems')]
    public function testRefusesInvalidItems(
        string $id,
        string $label,
        string $route,
        ?string $permission,
        ?string $parent,
        string $message,
    ): void {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        new AdminMenuItem($id, $label, $route, $permission, $parent);
    }
}

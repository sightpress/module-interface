<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Access;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Access\Viewer;
use SightPress\Module\Internal\Guard;

#[CoversClass(Viewer::class)]
#[UsesClass(Guard::class)]
final class ViewerTest extends TestCase
{
    public function testSignedInViewer(): void
    {
        $viewer = new Viewer(5, ['events.manage', 'admin.access', 'events.manage']);

        self::assertTrue($viewer->isSignedIn());
        self::assertSame(5, $viewer->userId);
        self::assertSame(['admin.access', 'events.manage'], $viewer->permissions);
        self::assertTrue($viewer->can('events.manage'));
        self::assertFalse($viewer->can('role.manage'));
    }

    public function testAVisitorWhoIsNotSignedInHasNoPermissions(): void
    {
        $viewer = new Viewer(null, ['events.manage']);

        self::assertFalse($viewer->isSignedIn());
        self::assertSame([], $viewer->permissions);
        self::assertFalse($viewer->can('events.manage'));
        self::assertEquals(Viewer::anonymous(), $viewer);
    }

    public function testFromRequest(): void
    {
        $request = new ServerRequest('GET', '/admin/events');
        $viewer = new Viewer(5, ['events.manage']);

        self::assertSame($viewer, Viewer::fromRequest($request->withAttribute(Viewer::class, $viewer)));
        self::assertNull(Viewer::fromRequest($request)->userId);
        self::assertNull(Viewer::fromRequest($request->withAttribute(Viewer::class, 'admin'))->userId);
    }
}

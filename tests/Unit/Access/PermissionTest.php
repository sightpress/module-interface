<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Access;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Access\Permission;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(Permission::class)]
#[UsesClass(Guard::class)]
final class PermissionTest extends TestCase
{
    public function testDefaults(): void
    {
        $permission = new Permission('page.publish', 'Publish pages');

        self::assertSame('page.publish', $permission->name);
        self::assertSame('Publish pages', $permission->label);
        self::assertSame('', $permission->description);
        self::assertSame([], $permission->defaultRoles);
    }

    public function testAllFields(): void
    {
        $permission = new Permission(
            'page.edit.own',
            'Edit own pages',
            'Edit pages you created, while they are drafts.',
            ['admin', 'editor', 'contributor'],
        );

        self::assertSame('Edit pages you created, while they are drafts.', $permission->description);
        self::assertSame(['admin', 'editor', 'contributor'], $permission->defaultRoles);
    }

    /**
     * @return iterable<string, array{string, string, list<mixed>, string}>
     */
    public static function invalidPermissions(): iterable
    {
        yield 'one part' => ['publish', 'Publish', [], 'Permission name is not valid ("publish")'];
        yield 'upper case' => ['Page.publish', 'Publish', [], 'Permission name is not valid ("Page.publish")'];
        yield 'empty label' => ['page.publish', ' ', [], 'Label of permission "page.publish" must not be empty.'];
        yield 'bad role' => ['page.publish', 'Publish', ['Editor'], 'Default role of permission "page.publish"'];
        yield 'role not a string' => ['page.publish', 'Publish', [true], 'Default roles of "page.publish"[0]'];
        yield 'duplicate role' => ['page.publish', 'Publish', ['admin', 'admin'], 'has duplicates: admin.'];
    }

    /**
     * @param list<mixed> $roles
     */
    #[DataProvider('invalidPermissions')]
    public function testRefusesInvalidPermissions(string $name, string $label, array $roles, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        /** @phpstan-ignore argument.type */
        new Permission($name, $label, defaultRoles: $roles);
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\ModuleManifest;
use SightPress\Module\ModuleRequirement;

#[CoversClass(ModuleManifest::class)]
#[UsesClass(ModuleRequirement::class)]
#[UsesClass(Guard::class)]
final class ModuleManifestTest extends TestCase
{
    public function testDefaults(): void
    {
        $manifest = new ModuleManifest('page', '1.0.0');

        self::assertSame('page', $manifest->id);
        self::assertSame('1.0.0', $manifest->version);
        self::assertSame([], $manifest->requires);
        self::assertFalse($manifest->required);
        self::assertFalse($manifest->enabledByDefault);
        self::assertFalse($manifest->isOnForNewSites());
    }

    public function testRequiredModulesAreAlwaysOnForNewSites(): void
    {
        self::assertTrue(new ModuleManifest('user', '1.0.0', required: true)->isOnForNewSites());
        self::assertTrue(new ModuleManifest('user', '1.0.0', required: true, enabledByDefault: false)->isOnForNewSites());
        self::assertTrue(new ModuleManifest('search', '1.0.0', enabledByDefault: true)->isOnForNewSites());
    }

    public function testKeepsRequirementsInOrder(): void
    {
        $page = new ModuleRequirement('page', '^1.0');
        $rbac = new ModuleRequirement('rbac');

        self::assertSame([$page, $rbac], new ModuleManifest('acme.events', '2.0.0-beta.1+build.7', [$page, $rbac])->requires);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validIds(): iterable
    {
        yield 'single word' => ['page'];
        yield 'vendor prefix' => ['acme.events'];
        yield 'hyphen and underscore' => ['acme-co.event_cards'];
        yield 'digits' => ['seo2'];
        yield '64 characters' => [str_repeat('a', 64)];
    }

    #[DataProvider('validIds')]
    public function testAcceptsValidIds(string $id): void
    {
        self::assertSame($id, new ModuleManifest($id, '1.0.0')->id);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidIds(): iterable
    {
        yield 'empty' => [''];
        yield 'upper case' => ['Page'];
        yield 'starts with a digit' => ['1page'];
        yield 'trailing separator' => ['acme.'];
        yield 'double separator' => ['acme..events'];
        yield 'slash' => ['acme/events'];
        yield '65 characters' => [str_repeat('a', 65)];
    }

    #[DataProvider('invalidIds')]
    public function testRefusesInvalidIds(string $id): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Module id');

        new ModuleManifest($id, '1.0.0');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidVersions(): iterable
    {
        yield 'empty' => [''];
        yield 'leading v' => ['v1.0.0'];
        yield 'two parts' => ['1.0'];
        yield 'leading zero' => ['01.0.0'];
        yield 'constraint' => ['^1.0.0'];
    }

    #[DataProvider('invalidVersions')]
    public function testRefusesInvalidVersions(string $version): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Version of module "page"');

        new ModuleManifest('page', $version);
    }

    public function testRefusesSomethingOtherThanRequirements(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Requirements of "page"[0] must be an instance of ' . ModuleRequirement::class);

        /** @phpstan-ignore argument.type */
        new ModuleManifest('page', '1.0.0', ['user']);
    }

    public function testRefusesASelfRequirement(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Module "page" must not require itself.');

        new ModuleManifest('page', '1.0.0', [new ModuleRequirement('page')]);
    }

    public function testRefusesDuplicateRequirements(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Requirements of "acme.events" has duplicates: page.');

        new ModuleManifest('acme.events', '1.0.0', [new ModuleRequirement('page'), new ModuleRequirement('page', '^2')]);
    }
}

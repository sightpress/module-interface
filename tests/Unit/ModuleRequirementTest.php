<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\ModuleRequirement;

#[CoversClass(ModuleRequirement::class)]
#[UsesClass(Guard::class)]
final class ModuleRequirementTest extends TestCase
{
    public function testAnyVersionByDefault(): void
    {
        $requirement = new ModuleRequirement('page');

        self::assertSame('page', $requirement->moduleId);
        self::assertSame('*', $requirement->constraint);
    }

    public function testTrimsTheConstraint(): void
    {
        self::assertSame('>=1.0 <3.0', new ModuleRequirement('page', ' >=1.0 <3.0 ')->constraint);
    }

    public function testRefusesAnInvalidModuleId(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Required module id is not valid ("Page")');

        new ModuleRequirement('Page');
    }

    public function testRefusesAnEmptyConstraint(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Version constraint for "page" must not be empty.');

        new ModuleRequirement('page', '  ');
    }
}

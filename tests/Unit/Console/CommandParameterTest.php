<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Console\CommandParameter;
use SightPress\Module\Console\CommandParameterType;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(CommandParameter::class)]
#[UsesClass(Guard::class)]
final class CommandParameterTest extends TestCase
{
    public function testDefaults(): void
    {
        $parameter = new CommandParameter('slug', 'Slug of the site.');

        self::assertSame('slug', $parameter->name);
        self::assertSame('Slug of the site.', $parameter->description);
        self::assertSame(CommandParameterType::Argument, $parameter->type);
        self::assertFalse($parameter->required);
    }

    public function testRequiredOption(): void
    {
        $parameter = new CommandParameter('admin-email', 'Email of the first admin.', CommandParameterType::Option, true);

        self::assertSame(CommandParameterType::Option, $parameter->type);
        self::assertTrue($parameter->required);
    }

    public function testRefusesAnInvalidName(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Command parameter name is not valid ("--force")');

        new CommandParameter('--force', 'Skip the question.', CommandParameterType::Flag);
    }

    public function testRefusesAnEmptyDescription(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Description of parameter "force" must not be empty.');

        new CommandParameter('force', '', CommandParameterType::Flag);
    }

    public function testRefusesARequiredFlag(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Flag "--force" can\'t be required.');

        new CommandParameter('force', 'Skip the question.', CommandParameterType::Flag, true);
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Console\CommandInput;
use SightPress\Module\Console\CommandParameter;
use SightPress\Module\Console\CommandParameterType;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Tests\Fake\ArrayCommandInput;

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
        self::assertSame('Slug of the site', $parameter->label, 'without the full stop');
        self::assertNull($parameter->default);
        self::assertSame([], $parameter->choices);
        self::assertNull($parameter->normalise);
        self::assertNull($parameter->describe);
        self::assertNull($parameter->when);
        self::assertFalse($parameter->secret);
        self::assertFalse($parameter->ask, 'optional parameters are not asked by default');
    }

    public function testTheLabelIsTheDescriptionWithoutItsColonUnlessGiven(): void
    {
        self::assertSame('Email address of the first admin', new CommandParameter('email', ' Email address of the first admin: ')->label);
        self::assertSame('Main language', new CommandParameter('locale', 'Main language of the site:', label: 'Main language')->label);
    }

    public function testRefusesABlankLabel(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Label of parameter "slug" must not be empty.');

        new CommandParameter('slug', 'Slug:', label: ' ');
    }

    public function testAQuestionWithChoicesADefaultAndRules(): void
    {
        $normalise = static fn (string $value, CommandInput $answers): string => mb_strtolower($value);
        $describe = static fn (string $value): ?string => $value === 'sqlite' ? 'a file' : null;
        $when = static fn (CommandInput $answers): bool => $answers->option('db') === null;
        $parameter = new CommandParameter(
            'db-engine',
            'Database engine:',
            CommandParameterType::Option,
            true,
            label: 'Engine',
            default: 'sqlite',
            choices: ['sqlite', 'postgresql'],
            normalise: $normalise,
            describe: $describe,
            when: $when,
        );

        self::assertSame('Engine', $parameter->label);
        self::assertSame('sqlite', $parameter->default);
        self::assertSame(['sqlite', 'postgresql'], $parameter->choices);
        self::assertSame($normalise, $parameter->normalise);
        self::assertSame($describe, $parameter->describe);
        self::assertSame($when, $parameter->when);
        self::assertTrue($parameter->ask, 'required parameters are asked by default');
        self::assertSame('a file', ($parameter->describe)('sqlite'));
        self::assertTrue(($parameter->when)(new ArrayCommandInput()));
        self::assertSame('x', ($parameter->normalise)('X', new ArrayCommandInput()));
    }

    public function testAnOptionalSecretCanBeAskedWithADefaultWorkedOutLater(): void
    {
        $default = static fn (CommandInput $answers): ?string => $answers->argument('slug');
        $parameter = new CommandParameter('db-password', 'Password:', CommandParameterType::Option, default: $default, secret: true, ask: true);

        self::assertTrue($parameter->secret);
        self::assertTrue($parameter->ask);
        self::assertSame($default, $parameter->default);
    }

    public function testAFlagCanBeAskedAsYesOrNo(): void
    {
        $parameter = new CommandParameter('create-db', 'Create the database?', CommandParameterType::Flag, default: 'yes', ask: true);

        self::assertSame('yes', $parameter->default);
        self::assertTrue($parameter->ask);
    }

    public function testAFlagDefaultIsYesOrNo(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The default of flag "--create-db" must be "yes" or "no".');

        new CommandParameter('create-db', 'Create the database?', CommandParameterType::Flag, default: 'maybe');
    }

    /**
     * @return iterable<string, array{\Closure(): CommandParameter}>
     */
    public static function flagExtras(): iterable
    {
        $flag = CommandParameterType::Flag;
        yield 'choices' => [static fn (): CommandParameter => new CommandParameter('force', 'Skip.', $flag, choices: ['a'])];
        yield 'normaliser' => [static fn (): CommandParameter => new CommandParameter('force', 'Skip.', $flag, normalise: static fn (string $value): string => $value)];
        yield 'description' => [static fn (): CommandParameter => new CommandParameter('force', 'Skip.', $flag, describe: static fn (string $value): ?string => null)];
        yield 'secret' => [static fn (): CommandParameter => new CommandParameter('force', 'Skip.', $flag, secret: true)];
    }

    /**
     * @param \Closure(): CommandParameter $create
     */
    #[DataProvider('flagExtras')]
    public function testAFlagIsOnlyAnsweredYesOrNo(\Closure $create): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Flag "--force" is answered yes or no: it can\'t have choices, a normaliser, a description or be secret.');

        $create();
    }

    public function testChoicesAreAListOfDistinctWords(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Choices of parameter "owner" has duplicates: person.');

        new CommandParameter('owner', 'Owner:', CommandParameterType::Option, choices: ['person', 'Person']);
    }

    public function testRefusesABlankChoice(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Choices of parameter "owner" must not be empty.');

        new CommandParameter('owner', 'Owner:', CommandParameterType::Option, choices: ['person', ' ']);
    }

    public function testRefusesChoicesThatAreNotAList(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Choices of parameter "owner" must be a list.');

        /** @phpstan-ignore argument.type */
        new CommandParameter('owner', 'Owner:', CommandParameterType::Option, choices: ['a' => 'person']);
    }

    public function testTheDefaultMustBeAChoice(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('The default of parameter "owner" must be one of its choices.');

        new CommandParameter('owner', 'Owner:', CommandParameterType::Option, default: 'company', choices: ['person', 'organization']);
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

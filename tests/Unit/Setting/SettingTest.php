<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Setting;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Setting\Setting;
use SightPress\Module\Setting\SettingType;

#[CoversClass(Setting::class)]
#[UsesClass(Guard::class)]
final class SettingTest extends TestCase
{
    public function testDefaults(): void
    {
        $setting = new Setting('events.intro', 'Introduction', SettingType::LongText);

        self::assertSame('events.intro', $setting->key);
        self::assertSame('Introduction', $setting->label);
        self::assertSame(SettingType::LongText, $setting->type);
        self::assertNull($setting->default);
        self::assertSame('', $setting->help);
        self::assertSame([], $setting->choices);
    }

    public function testBooleanDefaultsToFalse(): void
    {
        self::assertFalse(new Setting('seo.indexnow', 'Ping IndexNow', SettingType::Boolean)->default);
        self::assertTrue(new Setting('seo.indexnow', 'Ping IndexNow', SettingType::Boolean, true)->default);
    }

    public function testChoice(): void
    {
        $choices = ['allow_all' => 'Allow all', 'block_all' => 'Block all'];
        $setting = new Setting('seo.ai_crawlers', 'AI crawlers', SettingType::Choice, 'allow_all', 'Who may read.', $choices);

        self::assertSame($choices, $setting->choices);
        self::assertSame('allow_all', $setting->default);
        self::assertSame('Who may read.', $setting->help);
        self::assertNull(new Setting('seo.ai_crawlers', 'AI crawlers', SettingType::Choice, choices: $choices)->default);
    }

    /**
     * @return iterable<string, array{SettingType, string|int|bool|null}>
     */
    public static function fittingDefaults(): iterable
    {
        yield 'text' => [SettingType::Text, 'Hello'];
        yield 'text null' => [SettingType::Text, null];
        yield 'long text' => [SettingType::LongText, "Two\nlines"];
        yield 'integer' => [SettingType::Integer, 20];
        yield 'integer null' => [SettingType::Integer, null];
        yield 'boolean' => [SettingType::Boolean, false];
    }

    #[DataProvider('fittingDefaults')]
    public function testAcceptsDefaultsThatFitTheType(SettingType $type, string|int|bool|null $default): void
    {
        self::assertSame($default, new Setting('events.value', 'Value', $type, $default)->default);
    }

    /**
     * @return iterable<string, array{SettingType, string|int|bool|null, array<mixed>, string}>
     */
    public static function invalidSettings(): iterable
    {
        $fit = 'does not fit its type';
        yield 'text with int' => [SettingType::Text, 1, [], $fit . ' "text".'];
        yield 'long text with bool' => [SettingType::LongText, true, [], $fit . ' "long_text".'];
        yield 'integer with string' => [SettingType::Integer, '1', [], $fit . ' "integer".'];
        yield 'boolean with int' => [SettingType::Boolean, 0, [], $fit . ' "boolean".'];
        yield 'choice not in list' => [SettingType::Choice, 'c', ['a' => 'A'], 'it must be one of the choices'];
        yield 'choice with int' => [SettingType::Choice, 1, ['a' => 'A'], 'it must be one of the choices'];
        yield 'choices on text' => [SettingType::Text, null, ['a' => 'A'], 'Only choice settings can have choices'];
        yield 'no choices' => [SettingType::Choice, null, [], 'needs at least one choice.'];
        yield 'numeric value' => [SettingType::Choice, null, ['1' => 'One'], 'not whole numbers.'];
        yield 'empty value' => [SettingType::Choice, null, ['' => 'None'], 'not whole numbers.'];
        yield 'label not a string' => [SettingType::Choice, null, ['a' => 1], 'Label of choice "a" of setting "events.value" must be a string.'];
        yield 'empty label' => [SettingType::Choice, null, ['a' => ' '], 'Label of choice "a" of setting "events.value" must not be empty.'];
    }

    /**
     * @param array<mixed> $choices
     */
    #[DataProvider('invalidSettings')]
    public function testRefusesInvalidSettings(
        SettingType $type,
        string|int|bool|null $default,
        array $choices,
        string $message,
    ): void {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        /** @phpstan-ignore argument.type */
        new Setting('events.value', 'Value', $type, $default, choices: $choices);
    }

    public function testRefusesAnInvalidKey(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Setting key is not valid ("page_size")');

        new Setting('page_size', 'Page size', SettingType::Integer);
    }

    public function testRefusesAnEmptyLabel(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Label of setting "events.page_size" must not be empty.');

        new Setting('events.page_size', '', SettingType::Integer);
    }
}

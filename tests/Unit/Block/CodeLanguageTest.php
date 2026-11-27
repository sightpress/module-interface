<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Block;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Block\CodeLanguage;
use SightPress\Module\Block\CodeToken;
use SightPress\Module\Block\CodeTokenKind;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Tests\Fake\Module\ICalendarHighlighter;

#[CoversClass(CodeLanguage::class)]
#[CoversClass(CodeToken::class)]
#[UsesClass(Guard::class)]
final class CodeLanguageTest extends TestCase
{
    public function testCodeLanguage(): void
    {
        $language = new CodeLanguage('icalendar', 'iCalendar', ['ics'], ICalendarHighlighter::class);
        self::assertSame(['icalendar', 'iCalendar', ['ics'], ICalendarHighlighter::class], [$language->id, $language->label, $language->aliases, $language->highlighter]);

        $plain = new CodeLanguage('c++', 'C++');
        self::assertSame([[], null], [$plain->aliases, $plain->highlighter]);
        self::assertSame('f#', new CodeLanguage('f#', 'F#')->id);
    }

    public function testIdIsLowerCase(): void
    {
        $this->expectException(InvalidValueException::class);
        new CodeLanguage('Twig', 'Twig');
    }

    public function testAliasMustDifferFromTheId(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('duplicates');
        new CodeLanguage('yaml', 'YAML', ['yml', 'yaml']);
    }

    public function testAliasIsChecked(): void
    {
        $this->expectException(InvalidValueException::class);
        new CodeLanguage('yaml', 'YAML', ['Y M L']);
    }

    public function testHighlighterIsNotBlank(): void
    {
        $this->expectException(InvalidValueException::class);
        /** @phpstan-ignore argument.type (testing the run-time check) */
        new CodeLanguage('yaml', 'YAML', [], '');
    }

    public function testCodeToken(): void
    {
        $token = new CodeToken('BEGIN', CodeTokenKind::Keyword);
        self::assertSame(['BEGIN', CodeTokenKind::Keyword], [$token->text, $token->kind]);
        self::assertNull(new CodeToken(' ')->kind);

        $this->expectException(InvalidValueException::class);
        new CodeToken('');
    }
}

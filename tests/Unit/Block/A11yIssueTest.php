<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Block;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Block\A11yIssue;
use SightPress\Module\Block\A11ySeverity;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(A11yIssue::class)]
#[UsesClass(Guard::class)]
final class A11yIssueTest extends TestCase
{
    public function testDefaults(): void
    {
        $issue = new A11yIssue('image.alt_missing', 'Describe the image, or mark it as decorative.');

        self::assertSame('image.alt_missing', $issue->rule);
        self::assertSame('Describe the image, or mark it as decorative.', $issue->message);
        self::assertSame(A11ySeverity::Error, $issue->severity);
        self::assertNull($issue->field);
        self::assertSame([], $issue->wcag);
    }

    public function testAllFields(): void
    {
        $issue = new A11yIssue('video.captions', 'Add captions.', A11ySeverity::Warning, '/tracks/0/a~1b', ['1.2.2', '1.2.4']);

        self::assertSame(A11ySeverity::Warning, $issue->severity);
        self::assertSame('/tracks/0/a~1b', $issue->field);
        self::assertSame(['1.2.2', '1.2.4'], $issue->wcag);
    }

    /**
     * @return iterable<string, array{string, string, ?string, list<mixed>, string}>
     */
    public static function invalidIssues(): iterable
    {
        yield 'bad rule' => ['Alt', 'Fix it.', null, [], 'Accessibility rule id is not valid ("Alt")'];
        yield 'empty message' => ['image.alt', ' ', null, [], 'Message of accessibility issue "image.alt" must not be empty.'];
        yield 'field without slash' => ['image.alt', 'Fix it.', 'alt', [], 'Field of accessibility issue "image.alt"'];
        yield 'bad escape in field' => ['image.alt', 'Fix it.', '/a~2', [], 'Field of accessibility issue "image.alt"'];
        yield 'bad criterion' => ['image.alt', 'Fix it.', null, ['1.1'], 'WCAG criterion of accessibility issue "image.alt"'];
        yield 'criterion not a string' => ['image.alt', 'Fix it.', null, [111], 'WCAG criteria of "image.alt"[0] must be a string.'];
        yield 'duplicate criterion' => ['image.alt', 'Fix it.', null, ['1.1.1', '1.1.1'], 'has duplicates: 1.1.1.'];
    }

    /**
     * @param list<mixed> $wcag
     */
    #[DataProvider('invalidIssues')]
    public function testRefusesInvalidIssues(string $rule, string $message, ?string $field, array $wcag, string $error): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($error);

        /** @phpstan-ignore argument.type */
        new A11yIssue($rule, $message, field: $field, wcag: $wcag);
    }
}

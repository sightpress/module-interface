<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Seo;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Seo\SeoEntry;

#[CoversClass(SeoEntry::class)]
#[UsesClass(Guard::class)]
final class SeoEntryTest extends TestCase
{
    public function testDefaults(): void
    {
        $entry = new SeoEntry('/events/fado', 'en-GB', 'Fado night', new DateTimeImmutable('2026-11-01 10:00+01:00'));

        self::assertSame('/events/fado', $entry->path);
        self::assertSame('en-GB', $entry->locale);
        self::assertSame('Fado night', $entry->title);
        self::assertSame('2026-11-01T09:00:00+00:00', $entry->updatedAt->format(DATE_ATOM));
        self::assertNull($entry->description);
        self::assertNull($entry->markdownPath);
        self::assertSame([], $entry->alternates);
        self::assertSame([], $entry->structuredData);
    }

    public function testAllFields(): void
    {
        $node = ['@type' => 'Event', 'name' => 'Fado night'];
        $entry = new SeoEntry(
            '/events/fado',
            'en-GB',
            'Fado night',
            new DateTimeImmutable('2026-11-01T10:00:00Z'),
            'Live fado.',
            '/events/fado.md',
            ['pt-PT' => '/pt-pt/eventos/fado'],
            [$node],
        );

        self::assertSame('Live fado.', $entry->description);
        self::assertSame('/events/fado.md', $entry->markdownPath);
        self::assertSame(['pt-PT' => '/pt-pt/eventos/fado'], $entry->alternates);
        self::assertSame([$node], $entry->structuredData);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function invalid(): iterable
    {
        yield 'absolute URL' => [['path' => 'https://example.org/events'], 'SEO entry path is not valid'];
        yield 'protocol-relative' => [['path' => '//evil.test/x'], 'SEO entry path is not valid'];
        yield 'query' => [['path' => '/events?page=2'], 'SEO entry path is not valid'];
        yield 'blank title' => [['title' => ' '], 'SEO entry title must not be empty.'];
        yield 'blank description' => [['description' => ''], 'SEO entry description must not be empty.'];
        yield 'markdown not .md' => [['markdownPath' => '/events/fado.txt'], 'Markdown path is not valid'];
        yield 'alternate list' => [['alternates' => ['/pt-pt/x']], 'Alternates of an SEO entry must map a locale to a path.'];
        yield 'alternate locale' => [['alternates' => ['portuguese' => '/pt-pt/x']], 'Alternate locale is not valid'];
        yield 'alternate path' => [['alternates' => ['pt-PT' => 'pt-pt/x']], 'Alternate path is not valid'];
        yield 'nodes not a list' => [['structuredData' => ['event' => ['@type' => 'Event']]], 'must be a list of schema.org nodes'];
        yield 'node without type' => [['structuredData' => [['name' => 'x']]], 'Structured data node 0 of an SEO entry needs an "@type".'];
        yield 'node not an array' => [['structuredData' => ['Event']], 'Structured data node 0'];
    }

    /**
     * @param array<string, mixed> $fields
     */
    #[DataProvider('invalid')]
    public function testInvalidValuesAreRefused(array $fields, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        /** @phpstan-ignore argument.type (deliberately wrong values) */
        new SeoEntry(...[
            'path' => '/events/fado',
            'locale' => 'en-GB',
            'title' => 'Fado night',
            'updatedAt' => new DateTimeImmutable(),
            ...$fields,
        ]);
    }
}

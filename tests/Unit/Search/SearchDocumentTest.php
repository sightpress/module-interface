<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Search;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Search\SearchDocument;
use SightPress\Module\Search\SearchVisibility;

#[CoversClass(SearchDocument::class)]
#[UsesClass(Guard::class)]
final class SearchDocumentTest extends TestCase
{
    public function testDefaults(): void
    {
        $document = new SearchDocument('page', '42', 'pt-PT', '/pt-pt/sobre', 'Sobre nós', new DateTimeImmutable('2026-11-17T10:00:00+01:00'));

        self::assertSame('page', $document->source);
        self::assertSame('42', $document->ref);
        self::assertSame('pt-PT', $document->locale);
        self::assertSame('/pt-pt/sobre', $document->path);
        self::assertSame('Sobre nós', $document->title);
        self::assertSame('', $document->summary);
        self::assertSame('', $document->body);
        self::assertSame([], $document->breadcrumb);
        self::assertSame(SearchVisibility::Public, $document->visibility);
        self::assertSame('2026-11-17T09:00:00+00:00', $document->updatedAt->format(DATE_ATOM));
        self::assertSame('page:42:pt-PT', $document->key());
    }

    public function testAllFields(): void
    {
        $document = new SearchDocument(
            'acme.events',
            'fado',
            'zh-Hant-TW',
            '/',
            'Fado',
            new DateTimeImmutable('2026-11-17T09:00:00Z'),
            'A night of fado.',
            'Body text.',
            ['Events', 'Music'],
            SearchVisibility::Members,
        );

        self::assertSame('A night of fado.', $document->summary);
        self::assertSame('Body text.', $document->body);
        self::assertSame(['Events', 'Music'], $document->breadcrumb);
        self::assertSame(SearchVisibility::Members, $document->visibility);
        self::assertSame('members', $document->visibility->value);
    }

    /**
     * @return iterable<string, array{string, string, string, string, string, list<mixed>, string}>
     */
    public static function invalidDocuments(): iterable
    {
        yield 'bad source' => ['Pages', '1', 'en-GB', '/a', 'A', [], 'Search document source is not valid ("Pages")'];
        yield 'empty ref' => ['page', ' ', 'en-GB', '/a', 'A', [], 'Ref of search document from "page" must not be empty.'];
        yield 'bad locale' => ['page', '1', 'en_gb', '/a', 'A', [], 'Search document "page:1" locale is not valid ("en_gb")'];
        yield 'relative path' => ['page', '1', 'en-GB', 'a', 'A', [], 'Search document "page:1" path is not valid ("a")'];
        yield 'other host' => ['page', '1', 'en-GB', '//evil.example/a', 'A', [], 'path is not valid'];
        yield 'path with space' => ['page', '1', 'en-GB', '/a b', 'A', [], 'path is not valid'];
        yield 'empty title' => ['page', '1', 'en-GB', '/a', '', [], 'Search document "page:1" title must not be empty.'];
        yield 'crumb not a string' => ['page', '1', 'en-GB', '/a', 'A', [1], 'Search document "page:1" breadcrumb[0] must be a string.'];
        yield 'empty crumb' => ['page', '1', 'en-GB', '/a', 'A', [''], 'Search document "page:1" breadcrumb entry must not be empty.'];
    }

    /**
     * @param list<mixed> $breadcrumb
     */
    #[DataProvider('invalidDocuments')]
    public function testRefusesInvalidDocuments(
        string $source,
        string $ref,
        string $locale,
        string $path,
        string $title,
        array $breadcrumb,
        string $message,
    ): void {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);

        /** @phpstan-ignore argument.type (testing the run-time check) */
        new SearchDocument($source, $ref, $locale, $path, $title, new DateTimeImmutable(), breadcrumb: $breadcrumb);
    }
}

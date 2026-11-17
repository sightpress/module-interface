<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Search;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Search\SearchDocument;
use SightPress\Module\Search\SearchHit;

#[CoversClass(SearchHit::class)]
#[UsesClass(Guard::class)]
#[UsesClass(SearchDocument::class)]
final class SearchHitTest extends TestCase
{
    public function testDefaultsAndFields(): void
    {
        $document = self::document();

        $plain = new SearchHit($document);
        self::assertSame($document, $plain->document);
        self::assertSame(0.0, $plain->score);
        self::assertSame([], $plain->matchedWords);

        $hit = new SearchHit($document, 2.5, ['Fado', 'fado']);
        self::assertSame(2.5, $hit->score);
        self::assertSame(['Fado', 'fado'], $hit->matchedWords);
    }

    public function testRefusesANegativeScore(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Score of "page:1:en-GB" must be 0 or more.');

        new SearchHit(self::document(), -0.1);
    }

    public function testRefusesNotANumber(): void
    {
        $this->expectException(InvalidValueException::class);

        new SearchHit(self::document(), NAN);
    }

    public function testRefusesAnEmptyMatchedWord(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Matched word of "page:1:en-GB" must not be empty.');

        new SearchHit(self::document(), 1.0, [' ']);
    }

    private static function document(): SearchDocument
    {
        return new SearchDocument('page', '1', 'en-GB', '/fado', 'Fado', new DateTimeImmutable('2026-01-01'));
    }
}

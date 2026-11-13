<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use stdClass;

#[CoversClass(Guard::class)]
final class GuardTest extends TestCase
{
    public function testNotBlank(): void
    {
        self::assertSame(' a ', Guard::notBlank(' a ', 'Field'));

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field must not be empty.');
        Guard::notBlank(" \t", 'Field');
    }

    public function testMatches(): void
    {
        self::assertSame('abc', Guard::matches('abc', '/^[a-z]+$/', 'Field', 'letters only'));

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field is not valid ("ab1"): letters only.');
        Guard::matches('ab1', '/^[a-z]+$/', 'Field', 'letters only');
    }

    public function testMatchesRefusesEmptyEvenIfThePatternAllowsIt(): void
    {
        $this->expectException(InvalidValueException::class);
        Guard::matches('', '/^.*$/', 'Field', 'anything');
    }

    public function testUnique(): void
    {
        Guard::unique(['a', 'b'], 'Field');

        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field has duplicates: a, b.');
        Guard::unique(['a', 'b', 'a', 'c', 'b'], 'Field');
    }

    public function testListOf(): void
    {
        $object = new stdClass();
        self::assertSame([$object], Guard::listOf([$object], stdClass::class, 'Field'));
    }

    public function testListOfRefusesAMap(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field must be a list.');
        Guard::listOf(['a' => new stdClass()], stdClass::class, 'Field');
    }

    public function testListOfRefusesOtherTypes(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field[1] must be an instance of stdClass.');
        Guard::listOf([new stdClass(), 'x'], stdClass::class, 'Field');
    }

    public function testListOfStrings(): void
    {
        self::assertSame(['a', ''], Guard::listOfStrings(['a', ''], 'Field'));
    }

    public function testListOfStringsRefusesAMap(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field must be a list.');
        Guard::listOfStrings([1 => 'a'], 'Field');
    }

    public function testListOfStringsRefusesOtherTypes(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Field[0] must be a string.');
        Guard::listOfStrings([1], 'Field');
    }
}

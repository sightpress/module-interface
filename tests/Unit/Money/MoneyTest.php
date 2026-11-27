<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Money;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Money\Money;

#[CoversClass(Money::class)]
#[UsesClass(Guard::class)]
final class MoneyTest extends TestCase
{
    public function testMoneyCarriesItsCurrency(): void
    {
        $price = new Money(1250, 'GBP');

        self::assertSame([1250, 'GBP'], [$price->minorUnits, $price->currency]);
        self::assertTrue($price->equals(new Money(1250, 'GBP')));
        self::assertFalse($price->equals(new Money(1250, 'EUR')));
        self::assertFalse($price->equals(new Money(1251, 'GBP')));
        self::assertSame(-500, new Money(-500, 'JPY')->minorUnits);
    }

    public function testCurrencyIsAnIsoCode(): void
    {
        $this->expectException(InvalidValueException::class);
        new Money(1250, 'gbp');
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Placeholder;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Placeholder\Placeholder;
use SightPress\Module\Placeholder\PlaceholderType;
use SightPress\Module\Tests\Fake\Module\EventPlaceholders;
use SightPress\Module\Text\Message;

#[CoversClass(Placeholder::class)]
#[UsesClass(Guard::class)]
#[UsesClass(Message::class)]
final class PlaceholderTest extends TestCase
{
    public function testPlaceholder(): void
    {
        $placeholder = new Placeholder('acme_events.next_date', new Message('Next event date'), new Message('When the next event starts.'), PlaceholderType::DateTime, false, EventPlaceholders::class);

        self::assertSame(['acme_events.next_date', PlaceholderType::DateTime, false, EventPlaceholders::class], [$placeholder->name, $placeholder->type, $placeholder->personal, $placeholder->resolver]);
    }

    public function testNameNeedsTwoParts(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('at least two parts');
        new Placeholder('next_date', new Message('Next'), new Message('Next.'), PlaceholderType::Date, false, EventPlaceholders::class);
    }

    public function testResolverIsRequired(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Placeholder resolver');
        /** @phpstan-ignore argument.type (testing the run-time check) */
        new Placeholder('acme.next', new Message('Next'), new Message('Next.'), PlaceholderType::Date, false, ' ');
    }
}

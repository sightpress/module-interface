<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Text;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Text\Message;

#[CoversClass(Message::class)]
#[UsesClass(Guard::class)]
final class MessageTest extends TestCase
{
    public function testMessage(): void
    {
        $message = new Message('Deleted “{title}”.', ['title' => 'Fado night', 'count' => 2, 'share' => 0.5], 'acme_events');

        self::assertSame('Deleted “{title}”.', $message->text);
        self::assertSame(['title' => 'Fado night', 'count' => 2, 'share' => 0.5], $message->parameters);
        self::assertSame('acme_events', $message->domain);
        self::assertSame('messages', new Message('Saved.')->domain);
    }

    public function testParametersMustBeNamedTextOrNumbers(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Parameters of message "Hi {name}" must map names to text or numbers.');

        /** @phpstan-ignore argument.type */
        new Message('Hi {name}', ['name' => ['Ana']]);
    }

    public function testDomainIsAWord(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Translation domain is not valid ("Acme-Events")');

        new Message('Hi', domain: 'Acme-Events');
    }
}

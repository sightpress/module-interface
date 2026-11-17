<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Assistant;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Assistant\AssistantImage;
use SightPress\Module\Assistant\AssistantMessage;
use SightPress\Module\Assistant\AssistantRole;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(AssistantMessage::class)]
#[CoversClass(AssistantImage::class)]
#[UsesClass(Guard::class)]
final class AssistantMessageTest extends TestCase
{
    public function testNamedConstructors(): void
    {
        $image = new AssistantImage('image/webp', 'RIFF');

        self::assertSame(AssistantRole::System, AssistantMessage::system('Be brief.')->role);
        self::assertSame(AssistantRole::Assistant, AssistantMessage::assistant('Done.')->role);

        $user = AssistantMessage::user('Describe this image.', [$image]);
        self::assertSame(AssistantRole::User, $user->role);
        self::assertSame('Describe this image.', $user->text);
        self::assertSame([$image], $user->images);
        self::assertSame('image/webp', $image->mediaType);
        self::assertSame('RIFF', $image->data);
        self::assertSame([], AssistantMessage::user('Hi')->images);
    }

    public function testRefusesEmptyText(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Text of user message must not be empty.');

        AssistantMessage::user(' ');
    }

    public function testRefusesImagesOutsideUserMessages(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Only user messages can have images, not assistant messages.');

        new AssistantMessage(AssistantRole::Assistant, 'Look', [new AssistantImage('image/png', 'x')]);
    }

    public function testRefusesSomethingOtherThanImages(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Images of user message[0] must be an instance of');

        /** @phpstan-ignore argument.type (testing the run-time check) */
        AssistantMessage::user('Look', ['photo.png']);
    }

    public function testRefusesUnsupportedImageTypes(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Image type "image/svg+xml" is not supported: use one of image/jpeg, image/png, image/gif, image/webp.');

        new AssistantImage('image/svg+xml', '<svg/>');
    }

    public function testRefusesEmptyImages(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Image data must not be empty.');

        new AssistantImage('image/png', '');
    }
}

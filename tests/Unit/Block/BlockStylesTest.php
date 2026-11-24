<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Unit\Block;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Module\Block\BlockIcon;
use SightPress\Module\Block\BlockOption;
use SightPress\Module\Block\BlockStyles;
use SightPress\Module\Block\BlockVariant;
use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

#[CoversClass(BlockOption::class)]
#[CoversClass(BlockStyles::class)]
#[CoversClass(BlockVariant::class)]
#[CoversClass(BlockIcon::class)]
#[UsesClass(Guard::class)]
final class BlockStylesTest extends TestCase
{
    public function testEveryOptionHasItsDefaultFirst(): void
    {
        self::assertSame('start', BlockOption::Align->default());
        self::assertSame('transparent', BlockOption::Background->default());
        self::assertSame('content', BlockOption::Width->default());
        self::assertSame('medium', BlockOption::Spacing->default());
        self::assertSame('stacked', BlockOption::FormLayout->default());
        self::assertSame('medium', BlockOption::FormSize->default());
        self::assertSame(['start', 'end', 'stretch'], BlockOption::ButtonAlign->values());
        self::assertNotContains('justify', BlockOption::Align->values());
    }

    public function testStyles(): void
    {
        $styles = new BlockStyles(
            [new BlockVariant('plain', 'Plain'), new BlockVariant('card', 'Card')],
            [BlockOption::Align, BlockOption::FormLayout],
        );

        self::assertSame('plain', $styles->defaultVariant()?->name);
        self::assertSame('Card', $styles->variants[1]->label);
        self::assertTrue($styles->offers(BlockOption::Align));
        self::assertFalse($styles->offers(BlockOption::Background));
        self::assertSame([], $styles->values(BlockOption::Background));
        self::assertSame(['stacked', 'horizontal'], $styles->values(BlockOption::FormLayout));
        self::assertSame(['start', 'center', 'end'], $styles->values(BlockOption::Align));
        self::assertFalse($styles->shortForm);

        $search = new BlockStyles(options: [BlockOption::FormLayout], shortForm: true);
        self::assertNull($search->defaultVariant());
        self::assertSame(['stacked', 'horizontal', 'inline'], $search->values(BlockOption::FormLayout));
    }

    public function testIcon(): void
    {
        $icon = new BlockIcon('mastodon', 'Mastodon', '/srv/icons/mastodon.svg', ['mastodon.social', 'fosstodon.org'], '#6364ff');

        self::assertSame('mastodon', $icon->name);
        self::assertSame('Mastodon', $icon->label);
        self::assertSame('/srv/icons/mastodon.svg', $icon->svgFile);
        self::assertSame(['mastodon.social', 'fosstodon.org'], $icon->hosts);
        self::assertSame('#6364ff', $icon->brandColour);

        $plain = new BlockIcon('rss', 'RSS feed', 'C:\icons\rss.svg');
        self::assertSame([], $plain->hosts);
        self::assertNull($plain->brandColour);
    }

    /**
     * @return iterable<string, array{callable(): object, string}>
     */
    public static function invalid(): iterable
    {
        yield 'variant name' => [static fn (): object => new BlockVariant('Card', 'Card'), 'Variant name is not valid ("Card")'];
        yield 'variant label' => [static fn (): object => new BlockVariant('card', ''), 'Label of variant "card" must not be empty.'];
        yield 'duplicate variant' => [static fn (): object => new BlockStyles([new BlockVariant('card', 'Card'), new BlockVariant('card', 'Card 2')]), 'Block variants has duplicates: card.'];
        yield 'duplicate option' => [static fn (): object => new BlockStyles(options: [BlockOption::Align, BlockOption::Align]), 'Block options has duplicates: align.'];
        yield 'short form without layout' => [static fn (): object => new BlockStyles(shortForm: true), 'A short form must also offer the form_layout option.'];
        yield 'icon name' => [static fn (): object => new BlockIcon('Mastodon', 'Mastodon', '/a.svg'), 'Icon name is not valid'];
        yield 'icon file' => [static fn (): object => new BlockIcon('m', 'M', 'icons/m.svg'), 'SVG file of icon "m" is not valid'];
        yield 'icon png' => [static fn (): object => new BlockIcon('m', 'M', '/icons/m.png'), 'SVG file of icon "m" is not valid'];
        yield 'icon host' => [static fn (): object => new BlockIcon('m', 'M', '/m.svg', ['https://mastodon.social']), 'Host of icon "m" is not valid'];
        yield 'icon hosts unique' => [static fn (): object => new BlockIcon('m', 'M', '/m.svg', ['a.org', 'a.org']), 'Hosts of icon "m" has duplicates: a.org.'];
        yield 'icon colour' => [static fn (): object => new BlockIcon('m', 'M', '/m.svg', [], '#6364FF'), 'Brand colour of icon "m" is not valid'];
    }

    /**
     * @param callable(): object $create
     */
    #[DataProvider('invalid')]
    public function testInvalidValuesAreRefused(callable $create, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        $create();
    }
}

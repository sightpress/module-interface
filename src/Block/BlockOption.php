<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * Shared options a block type can offer editors, stored in each block's own data and drawn by core as classes on
 * the block's wrapper (never inline styles), which the theme styles through its tokens. A block type lists the ones
 * it offers in {@see BlockStyles::$options}.
 */
enum BlockOption: string
{
    /** Text alignment: `start` (default), `center` or `end`. Logical values, so right-to-left languages work. */
    case Align = 'align';

    /** Background from the theme's palette: `transparent` (default), `surface`, `muted` or `accent`. */
    case Background = 'background';

    /** Width: `content` (default), `wide` or `full`. */
    case Width = 'width';

    /** Space above and below: `none`, `small`, `medium` (default) or `large`. */
    case Spacing = 'spacing';

    /** For blocks that draw a form: `stacked` (default), `horizontal`, or `inline` for short forms. */
    case FormLayout = 'form_layout';

    /** For blocks that draw a form: `small`, `medium` (default) or `large`. */
    case FormSize = 'form_size';

    /** For blocks that draw a form: the button at the `start` (default), the `end`, or `stretch`ed. */
    case ButtonAlign = 'button_align';

    /**
     * The values editors can choose, the default first.
     *
     * @return non-empty-list<string>
     */
    public function values(): array
    {
        return match ($this) {
            self::Align => ['start', 'center', 'end'],
            self::Background => ['transparent', 'surface', 'muted', 'accent'],
            self::Width => ['content', 'wide', 'full'],
            self::Spacing => ['medium', 'none', 'small', 'large'],
            self::FormLayout => ['stacked', 'horizontal', 'inline'],
            self::FormSize => ['medium', 'small', 'large'],
            self::ButtonAlign => ['start', 'end', 'stretch'],
        };
    }

    public function default(): string
    {
        return $this->values()[0];
    }
}

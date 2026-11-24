<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * A block type with variants or shared options (since 0.5). Optional: a block type that doesn't implement it has
 * neither.
 *
 * Core stores the editor's choices next to the block's data and draws them as classes on the block's wrapper, so
 * {@see BlockType::render()} doesn't change. Implemented by modules.
 */
interface StyledBlockType extends BlockType
{
    public function styles(): BlockStyles;
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Block\BlockIcon;
use SightPress\Module\Block\BlockType;
use SightPress\Module\Block\BlockVariant;

/**
 * Adds block types that editors can use in page content, variants of existing block types, and icons.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface BlockTypeRegistrar
{
    /**
     * @param class-string<BlockType> $blockType core builds it from the site's container
     */
    public function add(string $blockType): void;

    /**
     * Adds a style variant to a block type that offers variants ({@see \SightPress\Module\Block\StyledBlockType}),
     * one of core's or another module's (since 0.5). The module that adds it ships the CSS for it, through the
     * theme tokens; on sites where the module is off, blocks that use it fall back to the default variant.
     *
     * @param string $blockType the block type's name, for example `quote`
     */
    public function addVariant(string $blockType, BlockVariant $variant): void;

    /**
     * Adds an icon that blocks with icons (such as `social_links`) can use (since 0.5). Its name must be unique.
     */
    public function addIcon(BlockIcon $icon): void;
}

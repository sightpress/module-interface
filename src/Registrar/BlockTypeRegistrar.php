<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Block\BlockType;

/**
 * Adds block types that editors can use in page content.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface BlockTypeRegistrar
{
    /**
     * @param class-string<BlockType> $blockType core builds it from the site's container
     */
    public function add(string $blockType): void;
}

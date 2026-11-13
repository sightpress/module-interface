<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Block\BlockType;
use SightPress\Module\Registrar\BlockTypeRegistrar;

final class InMemoryBlockTypeRegistrar implements BlockTypeRegistrar
{
    /** @var list<class-string<BlockType>> */
    public array $blockTypes = [];

    public function add(string $blockType): void
    {
        if (in_array($blockType, $this->blockTypes, true)) {
            throw DuplicateRegistration::of('Block type', $blockType);
        }
        $this->blockTypes[] = $blockType;
    }
}

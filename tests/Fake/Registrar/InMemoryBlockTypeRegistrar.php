<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Block\BlockIcon;
use SightPress\Module\Block\BlockType;
use SightPress\Module\Block\CodeLanguage;
use SightPress\Module\Block\BlockVariant;
use SightPress\Module\Registrar\BlockTypeRegistrar;

final class InMemoryBlockTypeRegistrar implements BlockTypeRegistrar
{
    /** @var list<class-string<BlockType>> */
    public array $blockTypes = [];

    /** @var array<string, array<string, BlockVariant>> block type → variant name → variant */
    public array $variants = [];

    /** @var array<string, BlockIcon> */
    public array $icons = [];

    /** @var array<string, CodeLanguage> id → language */
    public array $codeLanguages = [];

    public function add(string $blockType): void
    {
        if (in_array($blockType, $this->blockTypes, true)) {
            throw DuplicateRegistration::of('Block type', $blockType);
        }
        $this->blockTypes[] = $blockType;
    }

    public function addVariant(string $blockType, BlockVariant $variant): void
    {
        if (isset($this->variants[$blockType][$variant->name])) {
            throw DuplicateRegistration::of('Block variant', $blockType . ':' . $variant->name);
        }
        $this->variants[$blockType][$variant->name] = $variant;
    }

    public function addIcon(BlockIcon $icon): void
    {
        if (isset($this->icons[$icon->name])) {
            throw DuplicateRegistration::of('Icon', $icon->name);
        }
        $this->icons[$icon->name] = $icon;
    }

    public function addCodeLanguage(CodeLanguage $language): void
    {
        foreach ([$language->id, ...$language->aliases] as $id) {
            foreach ($this->codeLanguages as $known) {
                if ($id === $known->id || in_array($id, $known->aliases, true)) {
                    throw DuplicateRegistration::of('Code language', $id);
                }
            }
        }
        $this->codeLanguages[$language->id] = $language;
    }
}

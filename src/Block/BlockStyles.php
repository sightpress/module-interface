<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * The looks and shared options a block type offers ({@see StyledBlockType::styles()}). Every choice is stored in
 * that block's own data and changes only that block.
 */
final readonly class BlockStyles
{
    /** @var list<BlockVariant> */
    public array $variants;

    /** @var list<BlockOption> */
    public array $options;

    /**
     * @param list<BlockVariant> $variants  the block type's own variants, the default first; other modules can add
     *                                      more with {@see \SightPress\Module\Registrar\BlockTypeRegistrar::addVariant()}
     * @param list<BlockOption>  $options   the shared options it offers
     * @param bool               $shortForm the form has one or two fields, so the `inline` form layout may be offered
     *                                      (needs {@see BlockOption::FormLayout})
     */
    public function __construct(array $variants = [], array $options = [], public bool $shortForm = false)
    {
        $this->variants = Guard::listOf($variants, BlockVariant::class, 'Block variants');
        Guard::unique(array_map(static fn (BlockVariant $variant): string => $variant->name, $this->variants), 'Block variants');
        $this->options = Guard::listOf($options, BlockOption::class, 'Block options');
        Guard::unique(array_map(static fn (BlockOption $option): string => $option->value, $this->options), 'Block options');
        if ($shortForm && !in_array(BlockOption::FormLayout, $this->options, true)) {
            throw new InvalidValueException('A short form must also offer the form_layout option.');
        }
    }

    public function defaultVariant(): ?BlockVariant
    {
        return $this->variants[0] ?? null;
    }

    public function offers(BlockOption $option): bool
    {
        return in_array($option, $this->options, true);
    }

    /**
     * The values editors can choose for an option, the default first; `inline` only for short forms.
     *
     * @return list<string>
     */
    public function values(BlockOption $option): array
    {
        if (!$this->offers($option)) {
            return [];
        }
        if ($option === BlockOption::FormLayout && !$this->shortForm) {
            return array_values(array_diff($option->values(), ['inline']));
        }

        return $option->values();
    }
}

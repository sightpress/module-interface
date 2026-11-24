<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

use SightPress\Module\Internal\Guard;

/**
 * A named look of a block type, such as `card` for a quote. Drawn as the class `block--variant-<name>`, which the
 * theme styles. Editors see the label in text, not only a preview.
 */
final readonly class BlockVariant
{
    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $label;

    /**
     * @param string $name  lower-case letters, digits and `-`, for example `card`
     * @param string $label what editors see, for example `Card`
     */
    public function __construct(string $name, string $label)
    {
        $this->name = Guard::matches($name, '/^[a-z][a-z0-9-]*$/', 'Variant name', 'use lower-case letters, digits and "-", for example "card"');
        $this->label = Guard::notBlank($label, sprintf('Label of variant "%s"', $name));
    }
}

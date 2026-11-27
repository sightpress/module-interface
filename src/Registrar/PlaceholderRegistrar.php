<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Placeholder\Placeholder;

/**
 * Adds details editors can put in text (since 0.6). Names must be unique; `site.*` and `user.*` are core's.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface PlaceholderRegistrar
{
    public function add(Placeholder $placeholder): void;
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Placeholder\Placeholder;
use SightPress\Module\Registrar\PlaceholderRegistrar;

final class InMemoryPlaceholderRegistrar implements PlaceholderRegistrar
{
    /** @var array<string, Placeholder> */
    public array $placeholders = [];

    public function add(Placeholder $placeholder): void
    {
        if (isset($this->placeholders[$placeholder->name])) {
            throw DuplicateRegistration::of('Placeholder', $placeholder->name);
        }
        $this->placeholders[$placeholder->name] = $placeholder;
    }
}

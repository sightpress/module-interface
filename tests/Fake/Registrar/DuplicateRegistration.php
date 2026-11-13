<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use LogicException;

/**
 * Thrown by the in-memory registrars when something is registered twice under the same name, as core does.
 */
final class DuplicateRegistration extends LogicException
{
    public static function of(string $kind, string $name): self
    {
        return new self(sprintf('%s "%s" is already registered.', $kind, $name));
    }
}

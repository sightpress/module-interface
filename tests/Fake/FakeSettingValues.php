<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Setting\SettingValues;

final readonly class FakeSettingValues implements SettingValues
{
    /**
     * @param array<string, string|int|bool|null> $values
     */
    public function __construct(private array $values)
    {
    }

    public function get(string $key): string|int|bool|null
    {
        if (!array_key_exists($key, $this->values)) {
            throw new InvalidValueException(sprintf('No module registered the setting "%s".', $key));
        }

        return $this->values[$key];
    }
}

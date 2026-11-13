<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Console\CommandInput;

final readonly class ArrayCommandInput implements CommandInput
{
    /**
     * @param array<string, string> $arguments
     * @param array<string, string> $options
     * @param list<string>          $flags
     */
    public function __construct(
        private array $arguments = [],
        private array $options = [],
        private array $flags = [],
    ) {
    }

    public function argument(string $name): ?string
    {
        return $this->arguments[$name] ?? null;
    }

    public function option(string $name): ?string
    {
        return $this->options[$name] ?? null;
    }

    public function flag(string $name): bool
    {
        return in_array($name, $this->flags, true);
    }
}

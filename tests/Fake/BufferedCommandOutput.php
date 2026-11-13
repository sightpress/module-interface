<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Console\CommandOutput;

final class BufferedCommandOutput implements CommandOutput
{
    /** @var list<string> */
    public array $lines = [];

    /** @var list<string> */
    public array $errors = [];

    public function line(string $text = ''): void
    {
        $this->lines[] = $text;
    }

    public function error(string $text): void
    {
        $this->errors[] = $text;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\TranslationRegistrar;

final class InMemoryTranslationRegistrar implements TranslationRegistrar
{
    /** @var list<string> */
    public array $directories = [];

    public function add(string $directory): void
    {
        if (in_array($directory, $this->directories, true)) {
            throw DuplicateRegistration::of('Translation folder', $directory);
        }
        $this->directories[] = $directory;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Site\SiteLanguages;

final readonly class FakeSiteLanguages implements SiteLanguages
{
    /**
     * @param non-empty-list<non-empty-string> $locales the default one first
     */
    public function __construct(private array $locales = ['en-GB'])
    {
    }

    public function isMultilingual(): bool
    {
        return count($this->locales) > 1;
    }

    public function locales(): array
    {
        return $this->locales;
    }

    public function default(): string
    {
        return $this->locales[0];
    }
}

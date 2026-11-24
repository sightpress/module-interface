<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\SeoRegistrar;
use SightPress\Module\Seo\SeoProvider;

final class InMemorySeoRegistrar implements SeoRegistrar
{
    /** @var list<class-string<SeoProvider>> */
    public array $providers = [];

    public function addProvider(string $provider): void
    {
        if (in_array($provider, $this->providers, true)) {
            throw DuplicateRegistration::of('SEO provider', $provider);
        }
        $this->providers[] = $provider;
    }
}

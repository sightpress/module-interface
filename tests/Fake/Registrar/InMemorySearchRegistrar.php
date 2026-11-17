<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use LogicException;
use SightPress\Module\Registrar\SearchRegistrar;
use SightPress\Module\Search\SearchBackend;
use SightPress\Module\Search\SearchDocumentProvider;

final class InMemorySearchRegistrar implements SearchRegistrar
{
    /** @var list<class-string<SearchDocumentProvider>> */
    public array $providers = [];

    /** @var class-string<SearchBackend>|null */
    public ?string $backend = null;

    public function addProvider(string $provider): void
    {
        if (in_array($provider, $this->providers, true)) {
            throw DuplicateRegistration::of('Search document provider', $provider);
        }
        $this->providers[] = $provider;
    }

    public function useBackend(string $backend): void
    {
        if ($this->backend !== null) {
            throw new LogicException(sprintf('Search back-end "%s" is already in use.', $this->backend));
        }
        $this->backend = $backend;
    }
}

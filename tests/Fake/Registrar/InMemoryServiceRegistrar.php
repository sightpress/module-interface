<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use Closure;
use SightPress\Module\Registrar\ServiceRegistrar;

final class InMemoryServiceRegistrar implements ServiceRegistrar
{
    /** @var array<string, array{factory: Closure|null, class: class-string|null, public: bool}> */
    public array $services = [];

    public function factory(string $id, Closure $factory, bool $public = false): void
    {
        $this->store($id, ['factory' => $factory, 'class' => null, 'public' => $public]);
    }

    public function autowire(string $class, ?string $id = null, bool $public = false): void
    {
        $this->store($id ?? $class, ['factory' => null, 'class' => $class, 'public' => $public]);
    }

    /**
     * @param array{factory: Closure|null, class: class-string|null, public: bool} $definition
     */
    private function store(string $id, array $definition): void
    {
        if (isset($this->services[$id])) {
            throw DuplicateRegistration::of('Service', $id);
        }
        $this->services[$id] = $definition;
    }
}

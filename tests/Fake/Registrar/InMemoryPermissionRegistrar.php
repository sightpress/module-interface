<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Access\Permission;
use SightPress\Module\Registrar\PermissionRegistrar;

final class InMemoryPermissionRegistrar implements PermissionRegistrar
{
    /** @var array<string, Permission> */
    public array $permissions = [];

    public function add(Permission $permission): void
    {
        if (isset($this->permissions[$permission->name])) {
            throw DuplicateRegistration::of('Permission', $permission->name);
        }
        $this->permissions[$permission->name] = $permission;
    }
}

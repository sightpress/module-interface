<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Admin\AdminMenuItem;
use SightPress\Module\Registrar\AdminMenuRegistrar;

final class InMemoryAdminMenuRegistrar implements AdminMenuRegistrar
{
    /** @var array<string, AdminMenuItem> */
    public array $items = [];

    public function add(AdminMenuItem $item): void
    {
        if (isset($this->items[$item->id])) {
            throw DuplicateRegistration::of('Admin menu item', $item->id);
        }
        $this->items[$item->id] = $item;
    }
}

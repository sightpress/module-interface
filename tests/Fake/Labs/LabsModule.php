<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Labs;

use SightPress\Module\ModuleInterface;
use SightPress\Module\ModuleManifest;
use SightPress\Module\ModuleRegistrar;

/**
 * A third-party module that swaps in its own search back-end and adds an assistant provider. Off for new sites
 * unless an admin turns it on.
 */
final class LabsModule implements ModuleInterface
{
    public function manifest(): ModuleManifest
    {
        return new ModuleManifest('acme.labs', '0.1.0');
    }

    public function register(ModuleRegistrar $registrar): void
    {
        $registrar->search()->useBackend(InMemorySearchBackend::class);
        $registrar->assistant()->addProvider(EchoAssistantProvider::class);
        $registrar->sms()->addProvider(TestSmsProvider::class);
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Registrar\SmsRegistrar;
use SightPress\Module\Sms\SmsProvider;

final class InMemorySmsRegistrar implements SmsRegistrar
{
    /** @var list<class-string<SmsProvider>> */
    public array $providers = [];

    public function addProvider(string $provider): void
    {
        if (in_array($provider, $this->providers, true)) {
            throw DuplicateRegistration::of('Text message provider', $provider);
        }
        $this->providers[] = $provider;
    }
}

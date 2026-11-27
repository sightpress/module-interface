<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake\Registrar;

use SightPress\Module\Privacy\ConsentPurpose;
use SightPress\Module\Privacy\CookieDeclaration;
use SightPress\Module\Privacy\PersonalDataProvider;
use SightPress\Module\Privacy\ProcessorSource;
use SightPress\Module\Registrar\PrivacyRegistrar;

final class InMemoryPrivacyRegistrar implements PrivacyRegistrar
{
    /** @var list<class-string<PersonalDataProvider>> */
    public array $providers = [];

    /** @var array<string, CookieDeclaration> */
    public array $cookies = [];

    /** @var list<class-string<ProcessorSource>> */
    public array $processorSources = [];

    /** @var array<string, ConsentPurpose> */
    public array $consentPurposes = [];

    public function addProvider(string $provider): void
    {
        if (in_array($provider, $this->providers, true)) {
            throw DuplicateRegistration::of('Personal data provider', $provider);
        }
        $this->providers[] = $provider;
    }

    public function addCookie(CookieDeclaration $cookie): void
    {
        if (isset($this->cookies[$cookie->name])) {
            throw DuplicateRegistration::of('Cookie', $cookie->name);
        }
        $this->cookies[$cookie->name] = $cookie;
    }

    public function addProcessors(string $source): void
    {
        if (in_array($source, $this->processorSources, true)) {
            throw DuplicateRegistration::of('Processor source', $source);
        }
        $this->processorSources[] = $source;
    }

    public function addConsentPurpose(ConsentPurpose $purpose): void
    {
        if (isset($this->consentPurposes[$purpose->key])) {
            throw DuplicateRegistration::of('Consent purpose', $purpose->key);
        }
        $this->consentPurposes[$purpose->key] = $purpose;
    }
}

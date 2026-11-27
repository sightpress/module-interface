<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

use SightPress\Module\Privacy\ConsentPurpose;
use SightPress\Module\Privacy\CookieDeclaration;
use SightPress\Module\Privacy\PersonalDataProvider;
use SightPress\Module\Privacy\ProcessorSource;

/**
 * Declares the personal data a module keeps, the cookies it sets, the third parties it sends data to and what it
 * needs consent for (since 0.6). Every module that stores anything about people registers a provider for it.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface PrivacyRegistrar
{
    /**
     * @param class-string<PersonalDataProvider> $provider core builds it from the site's container; its data set key
     *                                                     must be unique
     */
    public function addProvider(string $provider): void;

    public function addCookie(CookieDeclaration $cookie): void;

    /**
     * @param class-string<ProcessorSource> $source core builds it from the site's container
     */
    public function addProcessors(string $source): void;

    public function addConsentPurpose(ConsentPurpose $purpose): void;
}

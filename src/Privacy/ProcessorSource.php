<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

/**
 * Says which third parties a module sends personal data to right now (since 0.6), registered with
 * {@see \SightPress\Module\Registrar\PrivacyRegistrar::addProcessors()}. Core asks it whenever it needs the list, so
 * it returns a provider only while the module is configured to use it (an SMS module without a key returns none).
 *
 * Implemented by modules; core builds it from the site's container.
 */
interface ProcessorSource
{
    /**
     * @return iterable<ProcessorDeclaration>
     */
    public function processors(): iterable;
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

/**
 * One data set of personal data a module keeps (since 0.6), registered with
 * {@see \SightPress\Module\Registrar\PrivacyRegistrar::addProvider()}. Core asks every provider of the modules that
 * are on when someone downloads their data or is erased, and prints {@see describe()} in the Privacy Policy, the
 * record of processing and *My account → Your data*.
 *
 * Implemented by modules; core builds it from the site's container.
 */
interface PersonalDataProvider
{
    public function describe(): PersonalDataSet;

    /**
     * The person's records, one array per record, with JSON-safe values only (strings, numbers, booleans, null and
     * arrays of them); dates as ISO 8601 strings. Only the person's own data: never someone else's.
     *
     * @return iterable<array<string, mixed>>
     */
    public function export(UserRef $user): iterable;

    /**
     * Deletes or pseudonymises the person's records and files as {@see PersonalDataSet::$erasure} says. Must be
     * safe to run again (an erasure is re-applied after a backup is restored), and must not log the data.
     */
    public function erase(UserRef $user): ErasureResult;
}

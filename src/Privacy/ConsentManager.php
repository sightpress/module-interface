<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Consent on the current site (since 0.6), in the site's container: a visitor's choices about cookies and browser
 * storage that aren't strictly necessary, and the opt-ins a signed-in person gave (stored with the text they agreed
 * to, and withdrawable from *My account*).
 *
 * Nothing is pre-ticked and saying no is as easy as saying yes. Core shows no banner while no module on the site
 * declares a cookie that needs consent.
 *
 * Implemented by core only; modules use it but must not implement it, so methods can be added in a minor release.
 */
interface ConsentManager
{
    /**
     * @return list<ConsentPurpose> the purposes of the modules that are on
     */
    public function purposes(): array;

    /**
     * Whether the visitor making the request said yes to the purpose. False until they choose, and false when the
     * browser sends `Sec-GPC: 1`.
     */
    public function allows(ServerRequestInterface $request, string $purpose): bool;

    /**
     * Whether the account gave consent for the purpose and hasn't withdrawn it.
     */
    public function hasConsented(int $userId, string $purpose): bool;

    /**
     * Stores that the account said yes, with the version of the text it was shown (for example a date, `2026-11-27`).
     */
    public function record(int $userId, string $purpose, string $textVersion): void;

    /**
     * Stores that the account withdrew its consent. Doing it twice changes nothing.
     */
    public function withdraw(int $userId, string $purpose): void;
}

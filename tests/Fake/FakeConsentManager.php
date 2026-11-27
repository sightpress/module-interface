<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use Psr\Http\Message\ServerRequestInterface;
use SightPress\Module\Privacy\ConsentManager;
use SightPress\Module\Privacy\ConsentPurpose;

/**
 * Core's consent rules in memory: a visitor's choices from a `consent` cookie (`purpose,purpose`), `Sec-GPC: 1` as
 * no, and account opt-ins with the text version they agreed to.
 */
final class FakeConsentManager implements ConsentManager
{
    /** @var array<int, array<string, string>> user id → purpose → text version */
    public array $consents = [];

    /**
     * @param list<ConsentPurpose> $purposes
     */
    public function __construct(private readonly array $purposes = [])
    {
    }

    public function purposes(): array
    {
        return $this->purposes;
    }

    public function allows(ServerRequestInterface $request, string $purpose): bool
    {
        if ($request->getHeaderLine('Sec-GPC') === '1') {
            return false;
        }
        $choice = $request->getCookieParams()['consent'] ?? '';

        return is_string($choice) && in_array($purpose, explode(',', $choice), true);
    }

    public function hasConsented(int $userId, string $purpose): bool
    {
        return isset($this->consents[$userId][$purpose]);
    }

    public function record(int $userId, string $purpose, string $textVersion): void
    {
        $this->consents[$userId][$purpose] = $textVersion;
    }

    public function withdraw(int $userId, string $purpose): void
    {
        unset($this->consents[$userId][$purpose]);
    }
}

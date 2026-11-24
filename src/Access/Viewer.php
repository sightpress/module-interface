<?php

declare(strict_types=1);

namespace SightPress\Module\Access;

use Psr\Http\Message\ServerRequestInterface;
use SightPress\Module\Internal\Guard;

/**
 * Who is making the request: their account id and their permissions on this site. Core puts it on every request
 * (`Viewer::fromRequest($request)`), so handlers can check access without depending on core.
 *
 * Signed-out visitors have no id and no permissions.
 */
final readonly class Viewer
{
    /** @var list<string> */
    public array $permissions;

    /**
     * @param int|null     $userId      the signed-in account; null for a visitor who isn't signed in
     * @param list<string> $permissions the permissions of the account's role, for example `events.manage`
     */
    public function __construct(
        public ?int $userId,
        array $permissions = [],
    ) {
        $permissions = Guard::listOfStrings($permissions, 'Permissions');
        $permissions = array_values(array_unique($permissions));
        sort($permissions);
        $this->permissions = $userId === null ? [] : $permissions;
    }

    public static function anonymous(): self
    {
        return new self(null);
    }

    /**
     * The viewer core put on the request, or a signed-out visitor when there is none (in a unit test, for example).
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        $viewer = $request->getAttribute(self::class);

        return $viewer instanceof self ? $viewer : self::anonymous();
    }

    public function isSignedIn(): bool
    {
        return $this->userId !== null;
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}

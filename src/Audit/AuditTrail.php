<?php

declare(strict_types=1);

namespace SightPress\Module\Audit;

/**
 * Writes to the site's audit log, which admins read on the Audit log screen. Record every change a person makes to
 * the site's content, settings or access. In the site's container as `AuditTrail::class`.
 *
 * Implemented by core only, so methods can be added in a minor release.
 */
interface AuditTrail
{
    public function record(AuditRecord $record): void;
}

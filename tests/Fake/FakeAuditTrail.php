<?php

declare(strict_types=1);

namespace SightPress\Module\Tests\Fake;

use SightPress\Module\Audit\AuditRecord;
use SightPress\Module\Audit\AuditTrail;

final class FakeAuditTrail implements AuditTrail
{
    /** @var list<AuditRecord> */
    public array $records = [];

    public function record(AuditRecord $record): void
    {
        $this->records[] = $record;
    }
}

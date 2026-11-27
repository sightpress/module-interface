<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use SightPress\Module\Internal\Guard;

/**
 * What {@see PersonalDataProvider::erase()} did, as counts only: core adds them to the audit log and the erasure
 * report, never the data.
 */
final readonly class ErasureResult
{
    /**
     * @param int $deleted       records deleted
     * @param int $pseudonymised records kept but moved to the *Former user* tombstone, without names or contact details
     * @param int $files         files deleted
     */
    public function __construct(
        public int $deleted = 0,
        public int $pseudonymised = 0,
        public int $files = 0,
    ) {
        Guard::notNegative($deleted, 'Deleted records');
        Guard::notNegative($pseudonymised, 'Pseudonymised records');
        Guard::notNegative($files, 'Deleted files');
    }

    /** Nothing was held about the person (or it was already erased). */
    public static function nothing(): self
    {
        return new self();
    }

    public function isEmpty(): bool
    {
        return $this->deleted === 0 && $this->pseudonymised === 0 && $this->files === 0;
    }
}

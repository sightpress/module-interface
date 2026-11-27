<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * How long a data set keeps a record: a number of days, or as long as the person's account exists. Printed in the
 * Privacy Policy and the record of processing; a module enforces its own retention (with a scheduled job).
 */
final readonly class Retention
{
    /** @var positive-int|null */
    public ?int $days;

    /** @var non-empty-string|null */
    public ?string $setting;

    /**
     * @param int|null    $days    days a record is kept after it was made or last used; null while the account
     *                             exists
     * @param string|null $setting the site setting that changes it (dotted name), when admins can
     */
    public function __construct(?int $days = null, ?string $setting = null)
    {
        if ($days !== null && $days < 1) {
            throw new InvalidValueException(sprintf('Retention must be at least 1 day (%d).', $days));
        }
        $this->days = $days;
        $this->setting = $setting === null ? null : Guard::matches($setting, Guard::DOTTED_NAME, 'Retention setting', 'use a dotted setting name such as "acme_events.retention_days"');
    }

    public static function whileAccountExists(): self
    {
        return new self();
    }

    public static function days(int $days, ?string $setting = null): self
    {
        return new self($days, $setting);
    }
}

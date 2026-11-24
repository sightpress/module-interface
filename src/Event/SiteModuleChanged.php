<?php

declare(strict_types=1);

namespace SightPress\Module\Event;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * A module was turned on or off for the site, or its data was deleted. Part of the stable event list (1.0 API).
 */
final readonly class SiteModuleChanged
{
    public const string ENABLED = 'enabled';
    public const string DISABLED = 'disabled';
    public const string DATA_DELETED = 'data_deleted';

    /**
     * @param string                                          $moduleId the module, for example `acme.events`
     * @param string                                          $name     its name as the Features screen shows it
     * @param self::ENABLED|self::DISABLED|self::DATA_DELETED $change
     * @param int|null                                        $actorId  the admin who did it; null for the command
     *                                                                  line
     */
    public function __construct(
        public string $moduleId,
        public string $name,
        public string $change,
        public ?int $actorId,
    ) {
        Guard::notBlank($moduleId, 'Module id');
        if (!in_array($change, [self::ENABLED, self::DISABLED, self::DATA_DELETED], true)) {
            throw new InvalidValueException(sprintf('Module change is not valid ("%s"): use enabled, disabled or data_deleted.', $change));
        }
    }
}

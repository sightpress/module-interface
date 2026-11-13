<?php

declare(strict_types=1);

namespace SightPress\Module;

use SightPress\Module\Internal\Guard;

/**
 * One module that another module needs, with the versions it works with.
 */
final readonly class ModuleRequirement
{
    /** @var non-empty-string */
    public string $moduleId;

    /** @var non-empty-string */
    public string $constraint;

    /**
     * @param string $moduleId   id of the required module (see {@see ModuleManifest::ID_PATTERN})
     * @param string $constraint Composer-style version constraint, for example `^1.2` or `>=1.0 <3.0`;
     *                           core checks it against the required module's manifest version
     */
    public function __construct(string $moduleId, string $constraint = '*')
    {
        $this->moduleId = Guard::matches(
            $moduleId,
            ModuleManifest::ID_PATTERN,
            'Required module id',
            ModuleManifest::ID_RULE,
        );
        $this->constraint = Guard::notBlank(trim($constraint), sprintf('Version constraint for "%s"', $moduleId));
    }
}

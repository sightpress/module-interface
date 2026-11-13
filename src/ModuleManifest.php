<?php

declare(strict_types=1);

namespace SightPress\Module;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * What core needs to know about a module before it registers anything: who it is, its version, which modules it
 * needs and whether it can be turned off.
 */
final readonly class ModuleManifest
{
    /**
     * Lower-case letters and digits, in parts joined by `.`, `-` or `_`; at most 64 characters.
     * Third-party modules should start with their vendor name, for example `acme.events`.
     */
    public const string ID_PATTERN = '/^(?=.{1,64}$)[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/';

    /** @internal Human-readable form of {@see ID_PATTERN}, used in error messages. */
    public const string ID_RULE = 'use 1 to 64 lower-case letters and digits, starting with a letter, in parts joined '
        . 'by ".", "-" or "_"';

    /**
     * Semantic Versioning 2.0.0 (https://semver.org), without a leading "v".
     */
    public const string VERSION_PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)'
        . '(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?'
        . '(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    /** @var non-empty-string */
    public string $id;

    /** @var non-empty-string */
    public string $version;

    /** @var list<ModuleRequirement> */
    public array $requires;

    /**
     * @param string                  $id               unique module id (see {@see ID_PATTERN})
     * @param string                  $version          the module's own version, SemVer 2.0.0
     * @param list<ModuleRequirement> $requires         modules that must be installed and on for this one to work;
     *                                                  core registers them first
     * @param bool                    $required         true for modules that are always on and can't be turned off
     *                                                  (core's `user`, `page`, `admin` …)
     * @param bool                    $enabledByDefault whether new sites start with the module on; ignored for
     *                                                  required modules, which are always on
     */
    public function __construct(
        string $id,
        string $version,
        array $requires = [],
        public bool $required = false,
        public bool $enabledByDefault = false,
    ) {
        $this->id = Guard::matches($id, self::ID_PATTERN, 'Module id', self::ID_RULE);
        $this->version = Guard::matches(
            $version,
            self::VERSION_PATTERN,
            sprintf('Version of module "%s"', $id),
            'use Semantic Versioning, for example 1.4.0',
        );
        $this->requires = Guard::listOf($requires, ModuleRequirement::class, sprintf('Requirements of "%s"', $id));

        $ids = array_map(static fn (ModuleRequirement $requirement): string => $requirement->moduleId, $this->requires);
        if (in_array($id, $ids, true)) {
            throw new InvalidValueException(sprintf('Module "%s" must not require itself.', $id));
        }
        Guard::unique($ids, sprintf('Requirements of "%s"', $id));
    }

    /**
     * Whether a new site starts with this module on: always for required modules, otherwise
     * {@see $enabledByDefault}.
     */
    public function isOnForNewSites(): bool
    {
        return $this->required || $this->enabledByDefault;
    }
}

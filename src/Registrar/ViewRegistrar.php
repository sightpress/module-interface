<?php

declare(strict_types=1);

namespace SightPress\Module\Registrar;

/**
 * Adds a folder of templates under a namespace, so templates can refer to them as `@<namespace>/<file>`.
 *
 * Templates must meet WCAG 2.2 AA and escape all output by default; see the module authoring guide.
 *
 * Implemented by core only; modules call it but must not implement it, so methods can be added in a minor release.
 */
interface ViewRegistrar
{
    /**
     * @param non-empty-string $namespace  lower-case letters, digits and `_`; usually the module id
     * @param non-empty-string $directory  absolute path to the folder
     */
    public function add(string $namespace, string $directory): void;
}

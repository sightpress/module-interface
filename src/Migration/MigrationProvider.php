<?php

declare(strict_types=1);

namespace SightPress\Module\Migration;

/**
 * Tells core where a module's database migrations are.
 *
 * Each module keeps its migrations in its own PHP namespace and folder, so their versions never clash with
 * another module's. Core runs a module's pending migrations when a site is created and when the module is turned
 * on. Migrations must work on every database engine SightPress supports (SQLite, MySQL, MariaDB and PostgreSQL),
 * so they describe the schema through core's portable schema API instead of raw engine-specific SQL.
 *
 * Implementations must return the same values every time and do no I/O.
 */
interface MigrationProvider
{
    /**
     * PHP namespace of the migration classes, for example `Acme\Events\Migrations`.
     *
     * @return non-empty-string
     */
    public function namespace(): string;

    /**
     * Absolute path to the folder that holds the migration classes.
     *
     * @return non-empty-string
     */
    public function directory(): string;
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Seo;

/**
 * Lists a module's public pages for the sitemap and `llms.txt`. Registered with
 * {@see \SightPress\Module\Registrar\SeoRegistrar::addProvider()}.
 *
 * Core calls it when it rebuilds those files, which it caches until content changes, so it may query the database.
 * Implemented by modules.
 */
interface SeoProvider
{
    /**
     * Every public, indexable page in one of the site's languages. Leave out drafts, members-only pages and pages
     * that ask search engines to stay away. Use a generator for long lists.
     *
     * @param string $locale BCP 47, for example `en-GB`
     *
     * @return iterable<SeoEntry>
     */
    public function entries(string $locale): iterable;
}

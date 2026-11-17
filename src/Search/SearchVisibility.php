<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

/**
 * Who may find a document in site search. Back-ends store it with the document and filter on it at query time.
 */
enum SearchVisibility: string
{
    /** Anyone may find it, including anonymous visitors. */
    case Public = 'public';

    /** Only people signed in to the site may find it (any role, `member` included). */
    case Members = 'members';
}

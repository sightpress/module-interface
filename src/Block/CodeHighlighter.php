<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * Splits code into {@see CodeToken}s for the `code` block (since 0.6). Joined, the tokens' text must be the code
 * exactly; core checks that, and shows the code as plain text if not.
 *
 * Implemented by modules; core builds it from the site's container.
 */
interface CodeHighlighter
{
    /**
     * @param string $language the {@see CodeLanguage::$id} the code is written in
     *
     * @return iterable<CodeToken>
     */
    public function highlight(string $code, string $language): iterable;
}

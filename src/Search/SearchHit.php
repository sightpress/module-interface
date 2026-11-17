<?php

declare(strict_types=1);

namespace SightPress\Module\Search;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * One result: the document that matched, how well, and which words in it matched.
 */
final readonly class SearchHit
{
    /** @var list<non-empty-string> */
    public array $matchedWords;

    /**
     * @param SearchDocument $document     the document as it was indexed
     * @param float          $score        relevance, 0 or more; higher is better. Only comparable within one
     *                                     set of results
     * @param list<string>   $matchedWords words as they appear in the document's text that matched the query
     *                                     (for example "Accessibility" for the query "accessib"), so core can
     *                                     build an extract and mark them
     */
    public function __construct(public SearchDocument $document, public float $score = 0.0, array $matchedWords = [])
    {
        if ($score < 0.0 || is_nan($score)) {
            throw new InvalidValueException(sprintf('Score of "%s" must be 0 or more.', $document->key()));
        }

        $words = [];
        foreach (Guard::listOfStrings($matchedWords, sprintf('Matched words of "%s"', $document->key())) as $word) {
            $words[] = Guard::notBlank($word, sprintf('Matched word of "%s"', $document->key()));
        }
        $this->matchedWords = $words;
    }
}

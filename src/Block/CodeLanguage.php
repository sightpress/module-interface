<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

use SightPress\Module\Internal\Guard;

/**
 * A language the `code` block offers (since 0.6), added with
 * {@see \SightPress\Module\Registrar\BlockTypeRegistrar::addCodeLanguage()}. Without a highlighter, code in it is
 * shown as plain text.
 */
final readonly class CodeLanguage
{
    private const string ID = '/^[a-z][a-z0-9+#._-]*$/';

    /** @var non-empty-string */
    public string $id;

    /** @var non-empty-string */
    public string $label;

    /** @var list<non-empty-string> */
    public array $aliases;

    /** @var class-string<CodeHighlighter>|null */
    public ?string $highlighter;

    /**
     * @param string                             $id          lower-case id, for example `twig`, `c++` or `f#`
     * @param string                             $label       the language's own name, for example "Twig" (not
     *                                                        translated)
     * @param list<string>                       $aliases     other ids people use, for example `yml` for `yaml`
     * @param class-string<CodeHighlighter>|null $highlighter splits its code into tokens; null for plain text
     */
    public function __construct(string $id, string $label, array $aliases = [], ?string $highlighter = null)
    {
        $rule = 'use lower-case letters, digits and "+", "#", ".", "_" or "-", for example "twig" or "c++"';
        $this->id = Guard::matches($id, self::ID, 'Code language id', $rule);
        $this->label = Guard::notBlank($label, 'Code language label');
        $checked = [];
        foreach (Guard::listOfStrings($aliases, 'Code language aliases') as $alias) {
            $checked[] = Guard::matches($alias, self::ID, 'Code language alias', $rule);
        }
        Guard::unique([$this->id, ...$checked], 'Code language id and aliases');
        $this->aliases = $checked;
        if ($highlighter !== null) {
            /** @var class-string<CodeHighlighter> $highlighter */
            $highlighter = Guard::notBlank($highlighter, 'Code highlighter');
        }
        $this->highlighter = $highlighter;
    }
}

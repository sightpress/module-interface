<?php

declare(strict_types=1);

namespace SightPress\Module\Site;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;

/**
 * The site a request or command works on. In the site's container as `SiteInfo::class`.
 */
final readonly class SiteInfo
{
    /** @var non-empty-string */
    public string $id;

    /** @var non-empty-string */
    public string $slug;

    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $defaultLocale;

    /** @var non-empty-list<non-empty-string> */
    public array $locales;

    /**
     * @param string       $id            the site's id; stable, use it to key data kept outside the site's database
     * @param string       $slug          its short name, as the command line uses it (`site:… <slug>`)
     * @param string       $name          its name, as visitors see it
     * @param string       $defaultLocale its main language (BCP 47)
     * @param list<string> $locales       every language it has, the main one included
     */
    public function __construct(string $id, string $slug, string $name, string $defaultLocale, array $locales)
    {
        $this->id = Guard::notBlank($id, 'Site id');
        $this->slug = Guard::notBlank($slug, 'Site slug');
        $this->name = Guard::notBlank($name, 'Site name');
        $this->defaultLocale = Guard::locale($defaultLocale, 'Default locale of the site');
        $checked = [];
        foreach (Guard::listOfStrings($locales, 'Site locales') as $locale) {
            $checked[] = Guard::locale($locale, 'Site locale');
        }
        if (!in_array($this->defaultLocale, $checked, true)) {
            throw new InvalidValueException(sprintf('The site\'s locales must include its default locale "%s".', $defaultLocale));
        }
        Guard::unique($checked, 'Site locales');
        /** @var non-empty-list<non-empty-string> $checked */
        $this->locales = $checked;
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use SightPress\Module\Exception\InvalidValueException;
use SightPress\Module\Internal\Guard;
use SightPress\Module\Text\Message;

/**
 * What a {@see PersonalDataProvider} keeps about people, in the words the Privacy Policy, the record of processing
 * and *My account → Your data* use.
 */
final readonly class PersonalDataSet
{
    /** @var non-empty-string */
    public string $key;

    /** @var non-empty-list<PersonalDataCategory> */
    public array $categories;

    /**
     * @param string                     $key          unique dotted name, starting with the module's prefix, for
     *                                                 example `acme_events.attendees`
     * @param Message                    $label        what it is, for example "Event bookings"
     * @param Message                    $purpose      why it is kept, in one plain sentence
     * @param list<PersonalDataCategory> $categories   the kinds of personal data in it
     * @param LawfulBasis                $lawfulBasis  the suggested lawful basis; the site owner decides
     * @param Retention                  $retention    how long records are kept
     * @param ErasureRule                $erasure      what erasing a person does to it
     * @param Message|null               $notes        anything else a reader needs, such as why records are kept
     */
    public function __construct(
        string $key,
        public Message $label,
        public Message $purpose,
        array $categories,
        public LawfulBasis $lawfulBasis,
        public Retention $retention,
        public ErasureRule $erasure = ErasureRule::Delete,
        public ?Message $notes = null,
    ) {
        $this->key = Guard::matches($key, Guard::DOTTED_NAME, 'Personal data set key', 'use a dotted name such as "acme_events.attendees"');
        $categories = Guard::listOf($categories, PersonalDataCategory::class, 'Personal data categories');
        if ($categories === []) {
            throw new InvalidValueException(sprintf('Personal data set "%s" needs at least one category.', $key));
        }
        Guard::unique(array_map(static fn (PersonalDataCategory $category): string => $category->value, $categories), 'Personal data categories');
        if ($erasure === ErasureRule::Keep && $notes === null) {
            throw new InvalidValueException(sprintf('Personal data set "%s" is kept on erasure: say why in its notes.', $key));
        }
        $this->categories = $categories;
    }
}

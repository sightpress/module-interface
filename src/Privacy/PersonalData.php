<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

use Attribute;
use SightPress\Module\Internal\Guard;

/**
 * Marks a property (or promoted constructor parameter) that holds personal data, naming the data set whose
 * {@see PersonalDataProvider} exports and erases it. Core's checks fail when a marked field's data set has no
 * provider (since 0.6).
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class PersonalData
{
    /** @var non-empty-string */
    public string $dataSet;

    /**
     * @param string                    $dataSet  the {@see PersonalDataSet::$key} it belongs to
     * @param PersonalDataCategory|null $category the kind of data in this field, when the data set has several
     */
    public function __construct(string $dataSet, public ?PersonalDataCategory $category = null)
    {
        $this->dataSet = Guard::matches($dataSet, Guard::DOTTED_NAME, 'Personal data set key', 'use a dotted name such as "acme_events.attendees"');
    }
}

<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

/**
 * What happens to a data set when a person is erased.
 */
enum ErasureRule: string
{
    /** The person's records are deleted. */
    case Delete = 'delete';

    /** The records stay, but point at the *Former user* tombstone and lose names and contact details. */
    case Pseudonymise = 'pseudonymise';

    /** The records stay as they are, for a reason the data set's notes give (for example, a legal duty). */
    case Keep = 'keep';
}

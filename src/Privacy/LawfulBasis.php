<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

/**
 * The six lawful bases for processing personal data (UK and EU GDPR, Art. 6(1)). A module gives the one it
 * *suggests*; the site owner decides, and the record of processing says so.
 */
enum LawfulBasis: string
{
    /** The person agreed to it, and can withdraw (Art. 6(1)(a)). */
    case Consent = 'consent';

    /** Needed to give the person what they asked for, such as an account (Art. 6(1)(b)). */
    case Contract = 'contract';

    /** Needed to meet a legal duty (Art. 6(1)(c)). */
    case LegalObligation = 'legal_obligation';

    /** Needed to protect someone's life (Art. 6(1)(d)). */
    case VitalInterests = 'vital_interests';

    /** Needed for a task in the public interest or official authority (Art. 6(1)(e)). */
    case PublicTask = 'public_task';

    /** Needed for a legitimate interest that the person's rights don't override, such as security (Art. 6(1)(f)). */
    case LegitimateInterests = 'legitimate_interests';
}

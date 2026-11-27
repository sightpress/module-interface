<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

/**
 * The kind of personal data in a data set, as the Privacy Policy and the record of processing group it.
 */
enum PersonalDataCategory: string
{
    /** Names, pictures and anything else that says who someone is. */
    case Identity = 'identity';

    /** Email addresses, phone numbers and postal addresses. */
    case Contact = 'contact';

    /** Sign-in details, roles, security codes and account settings. */
    case Account = 'account';

    /** IP addresses, user agents, device and session identifiers. */
    case Technical = 'technical';

    /** What someone did on the site: changes made, features used, counts. */
    case Usage = 'usage';

    /** Messages sent to or by the person. */
    case Communications = 'communications';

    /** Records of what someone agreed to, and when. */
    case Consent = 'consent';

    /** Things the person wrote or uploaded. */
    case Content = 'content';
}

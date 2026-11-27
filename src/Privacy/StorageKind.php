<?php

declare(strict_types=1);

namespace SightPress\Module\Privacy;

/**
 * Where something is kept in the visitor's browser. The law treats them alike: anything that isn't strictly
 * necessary waits for consent.
 */
enum StorageKind: string
{
    case Cookie = 'cookie';

    case LocalStorage = 'local_storage';

    case SessionStorage = 'session_storage';
}

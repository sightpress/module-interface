<?php

declare(strict_types=1);

namespace SightPress\Module\Block;

/**
 * What a piece of highlighted code is. Core styles each kind with a fixed palette that never relies on colour alone.
 */
enum CodeTokenKind: string
{
    case Keyword = 'keyword';

    case Type = 'type';

    case Name = 'name';

    case String = 'string';

    case Number = 'number';

    case Comment = 'comment';

    case Operator = 'operator';

    case Punctuation = 'punctuation';

    case Attribute = 'attribute';

    case Variable = 'variable';

    case Tag = 'tag';
}

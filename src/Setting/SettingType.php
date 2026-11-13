<?php

declare(strict_types=1);

namespace SightPress\Module\Setting;

/**
 * The kind of value a setting holds; core picks the form control from it.
 */
enum SettingType: string
{
    /** One line of text: `<input type="text">`. Default: string or null. */
    case Text = 'text';

    /** Several lines of plain text: `<textarea>`. Default: string or null. */
    case LongText = 'long_text';

    /** A whole number: `<input type="number">`. Default: int or null. */
    case Integer = 'integer';

    /** On or off: a checkbox. Default: bool. */
    case Boolean = 'boolean';

    /** One of a fixed list: a group of radio buttons in a `<fieldset>`. Default: one of the choices, or null. */
    case Choice = 'choice';
}

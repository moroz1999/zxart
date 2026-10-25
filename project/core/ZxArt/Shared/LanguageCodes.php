<?php

declare(strict_types=1);

namespace ZxArt\Shared;

/**
 * The language codes a production or release can carry. They are the values of
 * the `engine_zxitem_language.value` enum; adding one also needs a migration
 * extending that enum and the `language.<code>` translation in the SPA bundles.
 */
final class LanguageCodes
{
    /** @var list<string> */
    public const array ALL = [
        'be',
        'bs',
        'by',
        'ca',
        'cs',
        'da',
        'de',
        'el',
        'en',
        'eo',
        'es',
        'et',
        'eu',
        'fi',
        'fr',
        'gl',
        'hr',
        'hu',
        'is',
        'it',
        'ja',
        'la',
        'lt',
        'lv',
        'm-',
        'nl',
        'no',
        'pl',
        'pt',
        'ro',
        'ru',
        'sh',
        'sk',
        'sl',
        'sr',
        'sv',
        'tr',
        'ua',
        'he',
    ];
}

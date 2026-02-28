<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain;

enum PreferenceCode: string
{
    case THEME = 'theme';

    // Homepage layout
    case HOMEPAGE_ORDER = 'homepage_order';
    case HOMEPAGE_DISABLED = 'homepage_disabled';

    // Homepage per-module limits
    case HOMEPAGE_NEW_PRODS_LIMIT = 'homepage_new_prods_limit';
    case HOMEPAGE_NEW_PICTURES_LIMIT = 'homepage_new_pictures_limit';
    case HOMEPAGE_NEW_TUNES_LIMIT = 'homepage_new_tunes_limit';
    case HOMEPAGE_BEST_DEMOS_LIMIT = 'homepage_best_demos_limit';
    case HOMEPAGE_BEST_GAMES_LIMIT = 'homepage_best_games_limit';
    case HOMEPAGE_RECENT_PARTIES_LIMIT = 'homepage_recent_parties_limit';
    case HOMEPAGE_BEST_PICTURES_MONTH_LIMIT = 'homepage_best_pictures_month_limit';
    case HOMEPAGE_LATEST_PRODS_LIMIT = 'homepage_latest_prods_limit';
    case HOMEPAGE_LATEST_RELEASES_LIMIT = 'homepage_latest_releases_limit';
    case HOMEPAGE_SUPPORT_PRODS_LIMIT = 'homepage_support_prods_limit';
    case HOMEPAGE_UNVOTED_PICTURES_LIMIT = 'homepage_unvoted_pictures_limit';
    case HOMEPAGE_RANDOM_PICTURES_LIMIT = 'homepage_random_pictures_limit';
    case HOMEPAGE_UNVOTED_TUNES_LIMIT = 'homepage_unvoted_tunes_limit';
    case HOMEPAGE_RANDOM_TUNES_LIMIT = 'homepage_random_tunes_limit';

    // Homepage per-module minRating
    case HOMEPAGE_NEW_PRODS_MIN_RATING = 'homepage_new_prods_min_rating';
    case HOMEPAGE_BEST_DEMOS_MIN_RATING = 'homepage_best_demos_min_rating';
    case HOMEPAGE_BEST_GAMES_MIN_RATING = 'homepage_best_games_min_rating';

    // Radio
    case RADIO_CRITERIA = 'radio_criteria';

    // Interface language
    case LANGUAGE = 'language';
}

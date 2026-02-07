<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences\Domain;

enum HomepageModuleId: string
{
    case NEW_PRODS = 'newProds';
    case NEW_PICTURES = 'newPictures';
    case NEW_TUNES = 'newTunes';
    case BEST_NEW_DEMOS = 'bestNewDemos';
    case BEST_NEW_GAMES = 'bestNewGames';
    case RECENT_PARTIES = 'recentParties';
    case BEST_PICTURES_OF_MONTH = 'bestPicturesOfMonth';
    case LATEST_ADDED_PRODS = 'latestAddedProds';
    case LATEST_ADDED_RELEASES = 'latestAddedReleases';
    case SUPPORT_PRODS = 'supportProds';
    case UNVOTED_PICTURES = 'unvotedPictures';
    case RANDOM_GOOD_PICTURES = 'randomGoodPictures';
    case UNVOTED_TUNES = 'unvotedTunes';
    case RANDOM_GOOD_TUNES = 'randomGoodTunes';
}

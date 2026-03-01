<?php

declare(strict_types=1);

namespace ZxArt\UserPreferences;

use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\Domain\ThemeValue;

final class DefaultUserPreferencesProvider
{
    /**
     * @return array<string, string>
     */
    public function getDefaults(): array
    {
        return [
            PreferenceCode::THEME->value => ThemeValue::DARK->value,
            PreferenceCode::HOMEPAGE_ORDER->value => 'newProds,newPictures,newTunes,bestNewDemos,bestNewGames,recentParties,bestPicturesOfMonth,latestAddedProds,latestAddedReleases,supportProds,unvotedPictures,randomGoodPictures,unvotedTunes,randomGoodTunes',
            PreferenceCode::HOMEPAGE_DISABLED->value => '',
            PreferenceCode::HOMEPAGE_NEW_PRODS_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_NEW_PICTURES_LIMIT->value => '12',
            PreferenceCode::HOMEPAGE_NEW_TUNES_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_BEST_DEMOS_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_BEST_GAMES_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_RECENT_PARTIES_LIMIT->value => '5',
            PreferenceCode::HOMEPAGE_BEST_PICTURES_MONTH_LIMIT->value => '12',
            PreferenceCode::HOMEPAGE_LATEST_PRODS_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_LATEST_RELEASES_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_SUPPORT_PRODS_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_UNVOTED_PICTURES_LIMIT->value => '12',
            PreferenceCode::HOMEPAGE_RANDOM_PICTURES_LIMIT->value => '12',
            PreferenceCode::HOMEPAGE_UNVOTED_TUNES_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_RANDOM_TUNES_LIMIT->value => '10',
            PreferenceCode::HOMEPAGE_NEW_PRODS_START_YEAR->value => '1',
            PreferenceCode::HOMEPAGE_NEW_PRODS_MIN_RATING->value => '3.9',
            PreferenceCode::HOMEPAGE_BEST_DEMOS_MIN_RATING->value => '4',
            PreferenceCode::HOMEPAGE_BEST_GAMES_MIN_RATING->value => '4',
            PreferenceCode::RADIO_CRITERIA->value => '{}',
        ];
    }

    public function getDefault(PreferenceCode $code): ?string
    {
        return $this->getDefaults()[$code->value] ?? null;
    }
}

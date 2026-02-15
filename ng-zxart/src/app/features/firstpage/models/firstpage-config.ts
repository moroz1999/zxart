export type ModuleType =
  'newProds' | 'newPictures' | 'newTunes' |
  'bestNewDemos' | 'bestNewGames' |
  'recentParties' | 'bestPicturesOfMonth' |
  'latestAddedProds' | 'latestAddedReleases' |
  'supportProds' |
  'unvotedPictures' | 'randomGoodPictures' |
  'unvotedTunes' | 'randomGoodTunes';

export const ALL_MODULE_TYPES: ModuleType[] = [
  'newProds', 'newPictures', 'newTunes',
  'bestNewDemos', 'bestNewGames',
  'recentParties', 'bestPicturesOfMonth',
  'latestAddedProds', 'latestAddedReleases',
  'supportProds',
  'unvotedPictures', 'randomGoodPictures',
  'unvotedTunes', 'randomGoodTunes',
];

export interface ModuleSettings {
  limit: number;
  minRating?: number;
}

export interface ModuleConfig {
  type: ModuleType;
  enabled: boolean;
  order: number;
  settings: ModuleSettings;
}

export interface FirstpageConfig {
  modules: ModuleConfig[];
}

export const DEFAULT_MODULE_SETTINGS: Record<ModuleType, ModuleSettings> = {
  newProds: {limit: 10, minRating: 3.9},
  newPictures: {limit: 12},
  newTunes: {limit: 10},
  bestNewDemos: {limit: 10, minRating: 3},
  bestNewGames: {limit: 10, minRating: 3},
  recentParties: {limit: 5},
  bestPicturesOfMonth: {limit: 12},
  latestAddedProds: {limit: 10},
  latestAddedReleases: {limit: 10},
  supportProds: {limit: 10},
  unvotedPictures: {limit: 12},
  randomGoodPictures: {limit: 12},
  unvotedTunes: {limit: 10},
  randomGoodTunes: {limit: 10},
};

export const MODULE_LIMIT_PREF_CODES: Record<ModuleType, string> = {
  newProds: 'homepage_new_prods_limit',
  newPictures: 'homepage_new_pictures_limit',
  newTunes: 'homepage_new_tunes_limit',
  bestNewDemos: 'homepage_best_demos_limit',
  bestNewGames: 'homepage_best_games_limit',
  recentParties: 'homepage_recent_parties_limit',
  bestPicturesOfMonth: 'homepage_best_pictures_month_limit',
  latestAddedProds: 'homepage_latest_prods_limit',
  latestAddedReleases: 'homepage_latest_releases_limit',
  supportProds: 'homepage_support_prods_limit',
  unvotedPictures: 'homepage_unvoted_pictures_limit',
  randomGoodPictures: 'homepage_random_pictures_limit',
  unvotedTunes: 'homepage_unvoted_tunes_limit',
  randomGoodTunes: 'homepage_random_tunes_limit',
};

export const MODULE_MIN_RATING_PREF_CODES: Partial<Record<ModuleType, string>> = {
  newProds: 'homepage_new_prods_min_rating',
  bestNewDemos: 'homepage_best_demos_min_rating',
  bestNewGames: 'homepage_best_games_min_rating',
};

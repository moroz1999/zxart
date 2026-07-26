import {ModuleType} from './firstpage-config';

const DEMOS_CATEGORY_ID = 92159;
const GAMES_CATEGORY_ID = 92177;

export interface ModuleLinkConfig {
  titleKey: string;
  spaUrl: string;
}

export const MODULE_LINK_CONFIG: Record<ModuleType, ModuleLinkConfig | null> = {
  newProds: {spaUrl: '/prods?years=this', titleKey: 'firstpage.modules.viewAllButton.newProds'},
  bestNewDemos: {spaUrl: `/prods?cat=${DEMOS_CATEGORY_ID}&years=this`, titleKey: 'firstpage.modules.viewAllButton.bestNewDemos'},
  bestNewGames: {spaUrl: `/prods?cat=${GAMES_CATEGORY_ID}&years=this`, titleKey: 'firstpage.modules.viewAllButton.bestNewGames'},
  latestAddedProds: {spaUrl: '/prods?sorting=date%2Cdesc', titleKey: 'firstpage.modules.viewAllButton.latestAddedProds'},
  latestAddedReleases: {spaUrl: '/prods?sorting=date%2Cdesc&releases=1', titleKey: 'firstpage.modules.viewAllButton.latestAddedReleases'},
  supportProds: {spaUrl: `/prods?cat=${GAMES_CATEGORY_ID}&statuses=insales%2Cdonationware`, titleKey: 'firstpage.modules.viewAllButton.supportProds'},
  newPictures: {spaUrl: '/pictures/search', titleKey: 'firstpage.modules.viewAllButton.newPictures'},
  randomGoodPictures: {spaUrl: '/pictures/search?rating=4&sortParameter=votes&sortOrder=rand', titleKey: 'firstpage.modules.viewAllButton.randomGoodPictures'},
  newTunes: {spaUrl: '/music/search', titleKey: 'firstpage.modules.viewAllButton.newTunes'},
  randomGoodTunes: {spaUrl: '/music/search?rating=4&sortParameter=votes&sortOrder=rand', titleKey: 'firstpage.modules.viewAllButton.randomGoodTunes'},
  recentParties: null,
  bestPicturesOfMonth: null,
  bestTunesOfMonth: null,
  unvotedPictures: null,
  unvotedTunes: null,
};

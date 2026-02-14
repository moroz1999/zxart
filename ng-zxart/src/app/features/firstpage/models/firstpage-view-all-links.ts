import {ModuleType} from './firstpage-config';

export interface CatalogueBaseUrlsResponse {
  prodCatalogueBaseUrl: string | null;
  graphicsBaseUrl: string | null;
  musicBaseUrl: string | null;
}

export type CatalogueCategory = 'zxProd' | 'zxRelease' | 'graphics' | 'music';

export interface ModuleLinkConfig {
  category: CatalogueCategory;
  searchParams: string;
  titleKey: string;
}

export const MODULE_LINK_CONFIG: Record<ModuleType, ModuleLinkConfig | null> = {
  newProds: {category: 'zxProd', searchParams: 'software-categories/years:this/', titleKey: 'firstpage.modules.viewAllButton.newProds'},
  bestNewDemos: {category: 'zxProd', searchParams: 'demoscene/years:this/', titleKey: 'firstpage.modules.viewAllButton.bestNewDemos'},
  bestNewGames: {category: 'zxProd', searchParams: 'games/years:this/', titleKey: 'firstpage.modules.viewAllButton.bestNewGames'},
  latestAddedProds: {category: 'zxProd', searchParams: 'software-categories/sorting:date,desc/', titleKey: 'firstpage.modules.viewAllButton.latestAddedProds'},
  latestAddedReleases: {category: 'zxRelease', searchParams: 'software-categories/sorting:date,desc/', titleKey: 'firstpage.modules.viewAllButton.latestAddedReleases'},
  supportProds: {category: 'zxProd', searchParams: 'games/statuses:insales,donationware/', titleKey: 'firstpage.modules.viewAllButton.supportProds'},
  newPictures: {category: 'graphics', searchParams: 'sortParameter:date/sortOrder:desc/page:1/', titleKey: 'firstpage.modules.viewAllButton.newPictures'},
  randomGoodPictures: {category: 'graphics', searchParams: 'rating:4/sortParameter:votes/sortOrder:rand/page:1/', titleKey: 'firstpage.modules.viewAllButton.randomGoodPictures'},
  newTunes: {category: 'music', searchParams: 'sortParameter:date/sortOrder:desc/page:1/', titleKey: 'firstpage.modules.viewAllButton.newTunes'},
  randomGoodTunes: {category: 'music', searchParams: 'rating:4/sortParameter:votes/sortOrder:rand/page:1/', titleKey: 'firstpage.modules.viewAllButton.randomGoodTunes'},
  recentParties: null,
  bestPicturesOfMonth: null,
  unvotedPictures: null,
  unvotedTunes: null,
};

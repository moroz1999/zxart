import {Type} from '@angular/core';
import {ModuleType} from '../models/firstpage-config';
import {NewProdsComponent} from '../modules/new-prods/new-prods.component';
import {NewPicturesComponent} from '../modules/new-pictures/new-pictures.component';
import {NewTunesComponent} from '../modules/new-tunes/new-tunes.component';
import {BestNewDemosComponent} from '../modules/best-new-demos/best-new-demos.component';
import {BestNewGamesComponent} from '../modules/best-new-games/best-new-games.component';
import {RecentPartiesComponent} from '../modules/recent-parties/recent-parties.component';
import {BestPicturesOfMonthComponent} from '../modules/best-pictures-of-month/best-pictures-of-month.component';
import {LatestAddedProdsComponent} from '../modules/latest-added-prods/latest-added-prods.component';
import {LatestAddedReleasesComponent} from '../modules/latest-added-releases/latest-added-releases.component';
import {SupportProdsComponent} from '../modules/support-prods/support-prods.component';
import {UnvotedPicturesComponent} from '../modules/unvoted-pictures/unvoted-pictures.component';
import {RandomGoodPicturesComponent} from '../modules/random-good-pictures/random-good-pictures.component';
import {UnvotedTunesComponent} from '../modules/unvoted-tunes/unvoted-tunes.component';
import {RandomGoodTunesComponent} from '../modules/random-good-tunes/random-good-tunes.component';

export const MODULE_COMPONENTS: Record<ModuleType, Type<unknown>> = {
  newProds: NewProdsComponent,
  newPictures: NewPicturesComponent,
  newTunes: NewTunesComponent,
  bestNewDemos: BestNewDemosComponent,
  bestNewGames: BestNewGamesComponent,
  recentParties: RecentPartiesComponent,
  bestPicturesOfMonth: BestPicturesOfMonthComponent,
  latestAddedProds: LatestAddedProdsComponent,
  latestAddedReleases: LatestAddedReleasesComponent,
  supportProds: SupportProdsComponent,
  unvotedPictures: UnvotedPicturesComponent,
  randomGoodPictures: RandomGoodPicturesComponent,
  unvotedTunes: UnvotedTunesComponent,
  randomGoodTunes: RandomGoodTunesComponent,
};

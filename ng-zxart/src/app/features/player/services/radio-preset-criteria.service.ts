import {Injectable} from '@angular/core';
import {EMPTY_RADIO_CRITERIA, RadioCriteria} from '../models/radio-criteria';
import {RadioPreset} from '../models/radio-preset';
import {CurrentUserService} from '../../../shared/services/current-user.service';

const AVERAGE_VOTE = 3.8;
const MIN_RATING_OFFSET = 0.2;
const CATEGORY_IDS = {
  press: 244858,
  games: 92177,
  demoscene: 92159,
} as const;

@Injectable({
  providedIn: 'root'
})
export class RadioPresetCriteriaService {
  constructor(private currentUserService: CurrentUserService) {}

  buildCriteria(preset: RadioPreset): RadioCriteria {
    const minRating = AVERAGE_VOTE + MIN_RATING_OFFSET;
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const userId = this.currentUserService.userId ?? 0;

    switch (preset) {
      case 'randomgood':
        return {...EMPTY_RADIO_CRITERIA, minRating};
      case 'games':
        return {...EMPTY_RADIO_CRITERIA, minRating, prodCategoriesInclude: [CATEGORY_IDS.games]};
      case 'demoscene':
        return {...EMPTY_RADIO_CRITERIA, minRating, prodCategoriesInclude: [CATEGORY_IDS.demoscene]};
      case 'ay':
        return {...EMPTY_RADIO_CRITERIA, minRating, formatGroupsInclude: ['ay', 'aycovox', 'aydigitalay', 'ts']};
      case 'beeper':
        return {...EMPTY_RADIO_CRITERIA, minRating, formatGroupsInclude: ['beeper', 'aybeeper']};
      case 'exotic':
        return {
          ...EMPTY_RADIO_CRITERIA,
          minRating,
          formatGroupsInclude: ['digitalbeeper', 'tsfm', 'fm', 'digitalay', 'saa'],
        };
      case 'discover':
        return {
          ...EMPTY_RADIO_CRITERIA,
          bestVotesLimit: 100,
          notVotedByUserId: userId,
        };
      case 'underground':
        return {...EMPTY_RADIO_CRITERIA, bestVotesLimit: 500, maxPlays: 10};
      case 'lastyear':
        return {
          ...EMPTY_RADIO_CRITERIA,
          minRating,
          yearsInclude: month < 3 ? [year - 1, year] : [year],
        };
    }
  }
}

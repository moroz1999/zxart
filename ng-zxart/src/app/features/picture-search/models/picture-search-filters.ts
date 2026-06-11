export type PictureSearchResultsType = 'zxitem' | 'author';
export type PictureSearchSortOrder = 'asc' | 'desc' | 'rand';

export const PICTURE_SEARCH_SORT_PARAMETERS = [
  'date', 'year', 'title', 'place', 'votes', 'commentsAmount', 'views',
] as const;

export type PictureSearchSortParameter = typeof PICTURE_SEARCH_SORT_PARAMETERS[number];

export interface PictureSearchFilters {
  titleWord: string;
  startYear: string;
  endYear: string;
  rating: string;
  partyPlace: string;
  pictureType: string;
  realtime: boolean;
  inspiration: boolean;
  stages: boolean;
  tagsInclude: string[];
  tagsExclude: string[];
  authorCountryIds: number[];
  authorCityIds: number[];
  resultsType: PictureSearchResultsType;
  sortParameter: PictureSearchSortParameter;
  sortOrder: PictureSearchSortOrder;
}

export function createDefaultPictureSearchFilters(): PictureSearchFilters {
  return {
    titleWord: '',
    startYear: '',
    endYear: '',
    rating: '',
    partyPlace: '',
    pictureType: '',
    realtime: false,
    inspiration: false,
    stages: false,
    tagsInclude: [],
    tagsExclude: [],
    authorCountryIds: [],
    authorCityIds: [],
    resultsType: 'zxitem',
    sortParameter: 'date',
    sortOrder: 'desc',
  };
}

export function countActivePictureSearchFilters(filters: PictureSearchFilters): number {
  let count = 0;
  if (filters.titleWord.trim() !== '') {
    count++;
  }
  if (filters.startYear.trim() !== '') {
    count++;
  }
  if (filters.endYear.trim() !== '') {
    count++;
  }
  if (filters.rating.trim() !== '') {
    count++;
  }
  if (filters.partyPlace.trim() !== '') {
    count++;
  }
  if (filters.pictureType !== '') {
    count++;
  }
  if (filters.realtime) {
    count++;
  }
  if (filters.inspiration) {
    count++;
  }
  if (filters.stages) {
    count++;
  }
  count += filters.tagsInclude.length;
  count += filters.tagsExclude.length;
  count += filters.authorCountryIds.length;
  count += filters.authorCityIds.length;
  return count;
}

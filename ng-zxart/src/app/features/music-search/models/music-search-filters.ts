export type MusicSearchResultsType = 'zxitem' | 'author';
export type MusicSearchSortOrder = 'asc' | 'desc' | 'rand';

export const MUSIC_SEARCH_SORT_PARAMETERS = [
  'date', 'year', 'title', 'place', 'votes', 'commentsAmount', 'plays',
] as const;

export const MUSIC_SEARCH_FORMAT_GROUPS = [
  'ay', 'beeper', 'digitalbeeper', 'beeperdigitalbeeper', 'digitalay', 'ts',
  'fm', 'tsfm', 'aybeeper', 'aydigitalay', 'aycovox', 'saa',
] as const;

export type MusicSearchSortParameter = typeof MUSIC_SEARCH_SORT_PARAMETERS[number];

export interface MusicSearchFilters {
  titleWord: string;
  startYear: string;
  endYear: string;
  rating: string;
  partyPlace: string;
  formatGroup: string;
  format: string;
  realtime: boolean;
  tagsInclude: string[];
  tagsExclude: string[];
  authorCountryIds: number[];
  authorCityIds: number[];
  resultsType: MusicSearchResultsType;
  sortParameter: MusicSearchSortParameter;
  sortOrder: MusicSearchSortOrder;
}

export function createDefaultMusicSearchFilters(): MusicSearchFilters {
  return {
    titleWord: '',
    startYear: '',
    endYear: '',
    rating: '',
    partyPlace: '',
    formatGroup: '',
    format: '',
    realtime: false,
    tagsInclude: [],
    tagsExclude: [],
    authorCountryIds: [],
    authorCityIds: [],
    resultsType: 'zxitem',
    sortParameter: 'date',
    sortOrder: 'desc',
  };
}

export function countActiveMusicSearchFilters(filters: MusicSearchFilters): number {
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
  if (filters.formatGroup !== '') {
    count++;
  }
  if (filters.format !== '') {
    count++;
  }
  if (filters.realtime) {
    count++;
  }
  count += filters.tagsInclude.length;
  count += filters.tagsExclude.length;
  count += filters.authorCountryIds.length;
  count += filters.authorCityIds.length;
  return count;
}

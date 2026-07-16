import {Params} from '@angular/router';
import {
  createDefaultMusicSearchFilters,
  MUSIC_SEARCH_SORT_PARAMETERS,
  MusicSearchFilters,
  MusicSearchSortParameter,
} from './music-search-filters';

/**
 * Router query-param scheme for the SPA search entrypoint (`/music/search`).
 * Only non-default filters are emitted so shareable URLs stay clean.
 */

export interface ParsedMusicSearchParams {
  filters: MusicSearchFilters;
  page: number;
}

export function musicSearchFiltersToParams(filters: MusicSearchFilters, page: number): Params {
  const params: Params = {};
  setString(params, 'titleWord', filters.titleWord);
  setString(params, 'startYear', filters.startYear);
  setString(params, 'endYear', filters.endYear);
  setString(params, 'rating', filters.rating);
  setString(params, 'partyPlace', filters.partyPlace);
  setString(params, 'formatGroup', filters.formatGroup);
  setString(params, 'format', filters.format);
  if (filters.realtime) {
    params['realtime'] = '1';
  }
  setString(params, 'tagsInclude', filters.tagsInclude.join(','));
  setString(params, 'tagsExclude', filters.tagsExclude.join(','));
  setString(params, 'authorCountry', filters.authorCountryIds.join(','));
  setString(params, 'authorCity', filters.authorCityIds.join(','));
  if (filters.resultsType !== 'zxitem') {
    params['resultsType'] = filters.resultsType;
  }
  if (filters.sortParameter !== 'date') {
    params['sortParameter'] = filters.sortParameter;
  }
  if (filters.sortOrder !== 'desc') {
    params['sortOrder'] = filters.sortOrder;
  }
  if (page > 1) {
    params['page'] = String(page);
  }
  return params;
}

export function paramsToMusicSearchFilters(params: Params): ParsedMusicSearchParams {
  const filters = createDefaultMusicSearchFilters();
  filters.titleWord = readString(params['titleWord']);
  filters.startYear = readString(params['startYear']);
  filters.endYear = readString(params['endYear']);
  filters.rating = readString(params['rating']);
  filters.partyPlace = readString(params['partyPlace']);
  filters.formatGroup = readString(params['formatGroup']);
  filters.format = readString(params['format']);
  filters.realtime = params['realtime'] === '1';
  filters.tagsInclude = splitList(readString(params['tagsInclude']));
  filters.tagsExclude = splitList(readString(params['tagsExclude']));
  filters.authorCountryIds = splitIdList(readString(params['authorCountry']));
  filters.authorCityIds = splitIdList(readString(params['authorCity']));
  filters.resultsType = params['resultsType'] === 'author' ? 'author' : 'zxitem';
  filters.sortParameter = parseSortParameter(readString(params['sortParameter']));
  const sortOrder = readString(params['sortOrder']);
  filters.sortOrder = sortOrder === 'asc' || sortOrder === 'rand' ? sortOrder : 'desc';
  const page = Math.max(1, parseInt(readString(params['page']), 10) || 1);
  return {filters, page};
}

function setString(params: Params, name: string, value: string): void {
  const trimmed = value.trim();
  if (trimmed !== '') {
    params[name] = trimmed;
  }
}

function readString(value: unknown): string {
  return typeof value === 'string' ? value : '';
}

function splitList(value: string): string[] {
  return value.split(',').map(item => item.trim()).filter(item => item !== '');
}

function splitIdList(value: string): number[] {
  return splitList(value).map(item => parseInt(item, 10)).filter(id => Number.isFinite(id) && id > 0);
}

function parseSortParameter(value: string): MusicSearchSortParameter {
  const match = MUSIC_SEARCH_SORT_PARAMETERS.find(parameter => parameter === value);
  return match ?? 'date';
}

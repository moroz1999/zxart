import {
  createDefaultMusicSearchFilters,
  MUSIC_SEARCH_SORT_PARAMETERS,
  MusicSearchFilters,
  MusicSearchSortParameter,
} from './music-search-filters';

export interface ParsedMusicSearchUrl {
  urlBase: string;
  filters: MusicSearchFilters;
  page: number;
}

const SEGMENT_PATTERN = /^([a-zA-Z]+):(.+)$/;

export function parseMusicSearchUrl(pathname: string): ParsedMusicSearchUrl {
  const filters = createDefaultMusicSearchFilters();
  let page = 1;
  const baseSegments: string[] = [];

  for (const segment of pathname.split('/')) {
    const match = segment.match(SEGMENT_PATTERN);
    if (!match) {
      if (segment !== '') {
        baseSegments.push(segment);
      }
      continue;
    }
    const name = match[1];
    const value = decodeURIComponent(match[2]);
    switch (name) {
      case 'titleWord':
        filters.titleWord = value;
        break;
      case 'startYear':
        filters.startYear = value;
        break;
      case 'endYear':
        filters.endYear = value;
        break;
      case 'rating':
        filters.rating = value;
        break;
      case 'partyPlace':
        filters.partyPlace = value;
        break;
      case 'formatGroup':
        filters.formatGroup = value;
        break;
      case 'format':
        filters.format = value;
        break;
      case 'realtime':
        filters.realtime = value === '1';
        break;
      case 'tagsInclude':
        filters.tagsInclude = splitList(value);
        break;
      case 'tagsExclude':
        filters.tagsExclude = splitList(value);
        break;
      case 'authorCountry':
        filters.authorCountryIds = splitIdList(value);
        break;
      case 'authorCity':
        filters.authorCityIds = splitIdList(value);
        break;
      case 'resultsType':
        filters.resultsType = value === 'author' ? 'author' : 'zxitem';
        break;
      case 'sortParameter':
        filters.sortParameter = parseSortParameter(value);
        break;
      case 'sortOrder':
        filters.sortOrder = value === 'asc' || value === 'rand' ? value : 'desc';
        break;
      case 'page':
        page = Math.max(1, parseInt(value, 10) || 1);
        break;
      default:
        baseSegments.push(segment);
        break;
    }
  }

  const urlBase = '/' + baseSegments.join('/') + (baseSegments.length > 0 ? '/' : '');
  return {urlBase, filters, page};
}

export function buildMusicSearchPath(urlBase: string, filters: MusicSearchFilters, page: number): string {
  let path = urlBase;
  path += segment('titleWord', filters.titleWord);
  path += segment('startYear', filters.startYear);
  path += segment('endYear', filters.endYear);
  path += segment('rating', filters.rating);
  path += segment('partyPlace', filters.partyPlace);
  path += segment('formatGroup', filters.formatGroup);
  path += segment('format', filters.format);
  path += segment('sortParameter', filters.sortParameter);
  path += segment('sortOrder', filters.sortOrder);
  if (filters.realtime) {
    path += segment('realtime', '1');
  }
  path += segment('tagsInclude', filters.tagsInclude.join(','));
  path += segment('tagsExclude', filters.tagsExclude.join(','));
  path += segment('authorCountry', filters.authorCountryIds.join(','));
  path += segment('authorCity', filters.authorCityIds.join(','));
  path += segment('resultsType', filters.resultsType);
  if (page > 1) {
    path += segment('page', String(page));
  }
  return path;
}

function segment(name: string, value: string): string {
  const trimmed = value.trim();
  if (trimmed === '') {
    return '';
  }
  return name + ':' + encodeURIComponent(trimmed) + '/';
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

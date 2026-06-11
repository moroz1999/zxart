import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {AuthorListItem} from '../../author-browser/models/author-list-item';
import {MusicSearchResultsType} from './music-search-filters';

export interface MusicSearchResponse {
  totalAmount: number;
  resultsType: MusicSearchResultsType;
  tunes: ZxTuneDto[];
  authors: AuthorListItem[];
  formats: string[];
  apiUrl: string;
  zipUrl: string;
}

export interface MusicSearchLocation {
  id: number;
  title: string;
}

export interface MusicSearchLocationsResponse {
  items: MusicSearchLocation[];
}

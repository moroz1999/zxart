import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {AuthorListItem} from '../../author-browser/models/author-list-item';
import {PictureSearchResultsType} from './picture-search-filters';

export interface PictureSearchResponse {
  totalAmount: number;
  resultsType: PictureSearchResultsType;
  pictures: ZxPictureDto[];
  authors: AuthorListItem[];
  apiUrl: string;
  zipUrl: string;
}

export interface PictureSearchLocation {
  id: number;
  title: string;
}

export interface PictureSearchLocationsResponse {
  items: PictureSearchLocation[];
}

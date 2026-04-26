import {AuthorListItem} from '../../author-browser/models/author-list-item';
import {GroupListItem} from '../../group-browser/models/group-list-item';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';

export interface SearchAuthorRef {
  readonly title: string;
  readonly url: string;
}

export interface GenericSearchItemDto {
  readonly id: number;
  readonly type: string;
  readonly title: string;
  readonly titleHtml: string;
  readonly url: string;
  readonly snippetHtml: string | null;
  readonly year: number | null;
  readonly authors: SearchAuthorRef[];
}

export type SearchItemDto = AuthorListItem | GroupListItem | ZxPictureDto | ZxProdDto | GenericSearchItemDto;

export interface SearchResultSetDto {
  readonly type: string;
  readonly partial: boolean;
  readonly totalCount: number;
  readonly items: SearchItemDto[];
}

export interface SearchResultsDto {
  readonly phrase: string;
  readonly page: number;
  readonly pageSize: number;
  readonly total: number;
  readonly exactMatches: boolean;
  readonly sets: SearchResultSetDto[];
  readonly availableTypes: string[];
}

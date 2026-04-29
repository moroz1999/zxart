import {AuthorListItem} from '../../author-browser/models/author-list-item';
import {GroupListItem} from '../../group-browser/models/group-list-item';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ZxProdDto} from '../../../shared/models/zx-prod-dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {PartyDto} from '../../../shared/models/party-dto';

export interface PressArticleAuthorRef {
  readonly title: string;
  readonly url: string;
}

export interface PressArticleDto {
  readonly id: number;
  readonly title: string;
  readonly titleHtml: string;
  readonly url: string;
  readonly snippetHtml: string | null;
  readonly year: number | null;
  readonly authors: PressArticleAuthorRef[];
}

export type SearchItemDto =
  | AuthorListItem
  | GroupListItem
  | ZxPictureDto
  | ZxProdDto
  | ZxTuneDto
  | PressArticleDto
  | PartyDto;

export interface SearchResultSetDto {
  readonly type: string;
  readonly totalCount: number;
  readonly items: SearchItemDto[];
}

export interface SearchResultsDto {
  readonly phrase: string;
  readonly page: number;
  readonly pageSize: number;
  readonly total: number;
  readonly sets: SearchResultSetDto[];
}

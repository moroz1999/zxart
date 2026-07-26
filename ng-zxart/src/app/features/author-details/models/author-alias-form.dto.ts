import {EntityRef} from '../../../shared/models/entity-ref';

export interface AuthorAliasFormDto {
  author: EntityRef;
}

export interface AuthorAliasCreateDto {
  authorId: number;
  title: string;
  startDate: string;
  endDate: string;
  displayInMusic: boolean;
  displayInGraphics: boolean;
}

export interface AuthorAliasCreatedDto {
  id: number;
}

import {EntityRef} from '../../../shared/models/entity-ref';

export interface AuthorAliasFormDto {
  author: EntityRef;
  errorMessage?: string;
}

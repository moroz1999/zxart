import {AuthorDto} from './author-dto';

export interface ZxReleaseDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly year: string | null;
  readonly votes: number;
  readonly authors: AuthorDto[];
}

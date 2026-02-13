import {AuthorDto} from './author-dto';
import {PartyInfoDto} from './party-info-dto';

export interface ZxPictureDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly imageUrl: string;
  readonly imageLargeUrl?: string | null;
  readonly year: string | null;
  readonly authors: AuthorDto[];
  readonly party: PartyInfoDto | null;
  readonly isRealtime: boolean;
  readonly isFlickering: boolean;
  readonly compo: string | null;
  readonly votes: number;
  readonly votesAmount: number;
  readonly userVote: number | null;
  readonly denyVoting: boolean;
  readonly commentsAmount: number;
}

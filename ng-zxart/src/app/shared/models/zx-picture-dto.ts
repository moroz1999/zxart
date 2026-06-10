import {AuthorDto} from './author-dto';
import {PartyInfoDto} from './party-info-dto';
import {ReleaseInfoDto} from './release-info-dto';

export interface ZxPictureDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly imageUrl: string;
  readonly largeImageUrl: string;
  readonly fileId: number;
  readonly type: string;
  readonly pictureBorder: number;
  readonly palette: string;
  readonly rotation: number | null;
  readonly year: string | null;
  readonly authors: AuthorDto[];
  readonly party: PartyInfoDto | null;
  readonly release: ReleaseInfoDto | null;
  readonly isRealtime: boolean;
  readonly isFlickering: boolean;
  readonly compo: string | null;
  readonly votes: number;
  readonly votesAmount: number;
  readonly userVote: number | null;
  readonly denyVoting: boolean;
  readonly commentsAmount: number;
  readonly views: number;
}

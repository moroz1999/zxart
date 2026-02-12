import {AuthorDto} from './author-dto';
import {PartyInfoDto} from './party-info-dto';
import {ReleaseInfoDto} from './release-info-dto';

export interface ZxTuneDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly authors: AuthorDto[];
  readonly format: string;
  readonly year: string | null;
  readonly votes: number;
  readonly votesAmount: number;
  readonly userVote: number | null;
  readonly denyVoting: boolean;
  readonly commentsAmount: number;
  readonly plays: number;
  readonly party: PartyInfoDto | null;
  readonly release: ReleaseInfoDto | null;
  readonly isPlayable: boolean;
  readonly isRealtime: boolean;
  readonly compo: string | null;
  readonly mp3Url: string | null;
  readonly originalFileUrl: string | null;
  readonly trackerFileUrl: string | null;
}

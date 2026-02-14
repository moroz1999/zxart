import {AuthorDto} from './author-dto';
import {PartyInfoDto} from './party-info-dto';
import {ZxProdConnectedItems} from './zx-prod-dto';

export type LegalStatus =
  'unknown' | 'allowed' | 'allowedzxart' | 'forbidden' | 'forbiddenzxart' |
  'insales' | 'mia' | 'unreleased' | 'recovered' | 'donationware';

export interface FirstpageProdDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly year: string | null;
  readonly imageUrl: string | null;
  readonly hardwareInfo?: ZxProdConnectedItems;
  readonly votes: number;
  readonly votesAmount: number;
  readonly userVote: number | null;
  readonly denyVoting: boolean;
  readonly authors: AuthorDto[];
  readonly categories: string[];
  readonly party: PartyInfoDto | null;
  readonly legalStatus: LegalStatus | null;
}

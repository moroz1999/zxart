import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

export interface TuneTagDto {
  readonly title: string;
  readonly url: string;
}

export interface TunePartyContextDto {
  readonly title: string;
  readonly url: string;
  readonly place: number | null;
  readonly compoLabel: string | null;
}

export interface TuneSubmitterDto {
  readonly userName: string;
  readonly url: string;
}

/** A single downloadable artifact (original module, tracker file, rendered audio). */
export interface TuneDownloadDto {
  readonly id: string;
  readonly ext: string;
  readonly label: string;
  readonly sub: string | null;
  readonly size: string | null;
  readonly url: string;
}

export interface TuneDetailsDto extends ZxTuneDto {
  readonly description: string | null;
  readonly tags: TuneTagDto[];
  readonly partyContext: TunePartyContextDto | null;
  readonly chip: string | null;
  readonly channelsType: string | null;
  readonly channels: number | null;
  readonly duration: string | null;
  readonly container: string | null;
  readonly tracker: string | null;
  readonly internalTitle: string | null;
  readonly internalAuthor: string | null;
  readonly frequency: string | null;
  readonly intFrequency: string | null;
  readonly fileName: string | null;
  readonly converterVersion: string | null;
  readonly dateCreated: string | null;
  readonly submitter: TuneSubmitterDto | null;
  readonly downloads: TuneDownloadDto[];
}

export type TuneRelatedRailKind = 'author' | 'tags' | 'tracker';

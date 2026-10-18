import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {PageMetadataDto} from '../../../shared/models/page-metadata.dto';

export interface TuneTagDto {
  readonly id: number;
  readonly title: string;
}

export interface TunePartyContextDto {
  readonly title: string;
  readonly url: string;
  readonly place: number | null;
  readonly compoLabel: string | null;
}

export interface TuneSubmitterDto {
  readonly userName: string;
  readonly url: string | null;
}

/** A single downloadable artifact (original module, tracker file, rendered audio). */
/** What a downloadable file is; labelled through `tune-details.download-kind.<kind>`. */
export type TuneDownloadKind = 'original' | 'tracker' | 'executable' | 'audio';

export interface TuneDownloadDto {
  readonly id: string;
  readonly kind: TuneDownloadKind;
  readonly ext: string;
  /** The uploaded file's own name. */
  readonly fileName: string | null;
  readonly size: string | null;
  readonly url: string;
}

export interface TuneDetailsDto extends ZxTuneDto {
  /** Sound-group code chosen by the uploader; labelled from `player.formatGroup.*`. */
  readonly formatGroup: string | null;
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
  readonly metadata: PageMetadataDto;
}

export type TuneRelatedRailKind = 'author' | 'tags' | 'tracker';

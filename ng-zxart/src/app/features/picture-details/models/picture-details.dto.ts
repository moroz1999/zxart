import {AuthorDto} from '../../../shared/models/author-dto';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {PageMetadataDto} from '../../../shared/models/page-metadata.dto';

export interface PictureTagDto {
  readonly id: number;
  readonly title: string;
}

export interface PicturePartyContextDto {
  readonly title: string;
  readonly url: string;
  readonly place: number | null;
  readonly compoLabel: string | null;
}

export interface PictureProdContextDto {
  readonly title: string;
  readonly url: string;
  readonly year: string | null;
}

/** What a downloadable file is; labelled through `picture-details.download-kind.<kind>`. */
export type PictureDownloadKind = 'original' | 'pc' | 'print' | 'executable' | 'stages';

/** A single downloadable artifact (original file, rendered PNG, print, executable, drawing stages). */
export interface PictureDownloadDto {
  readonly id: string;
  readonly kind: PictureDownloadKind;
  readonly ext: string;
  /** Magnification of a PC render. */
  readonly zoom: number | null;
  /** The uploaded file's own name, for materials. */
  readonly fileName: string | null;
  readonly size: string | null;
  readonly url: string;
}

/** Reference / inspiration material shown in the "Материалы и референсы" section. */
export interface PictureMaterialDto {
  readonly id: string;
  readonly kind: 'inspiration' | 'sketch' | 'photo';
  readonly label: string;
  readonly imageUrl: string;
}

export interface PictureMentionDto {
  readonly title: string;
  readonly url: string;
}

export interface PictureTechInfoDto {
  readonly label: string;
  readonly value: string;
}

export type PictureRelatedRailKind = 'prod' | 'author' | 'tags';

/** A lazily-loaded related rail (items fetched per kind on demand). */
export interface PictureRelatedRailDto {
  readonly kind: PictureRelatedRailKind;
  readonly items: ZxPictureDto[];
}

export interface PictureSubmitterDto {
  readonly userName: string;
  readonly url: string | null;
}

export interface PictureDetailsDto extends ZxPictureDto {
  readonly metadata: PageMetadataDto;
  readonly description: string | null;
  readonly originalAuthors: AuthorDto[];
  readonly tags: PictureTagDto[];
  readonly partyContext: PicturePartyContextDto | null;
  readonly prodContext: PictureProdContextDto | null;
  readonly formatLabel: string;
  readonly paletteLabel: string;
  readonly resolution: string | null;
  readonly originalName: string | null;
  readonly views: number;
  readonly submitter: PictureSubmitterDto | null;
  readonly dateCreated: string | null;
  readonly downloads: PictureDownloadDto[];
  readonly materials: PictureMaterialDto[];
  readonly techInfo: PictureTechInfoDto[];
  readonly sequenceUrl: string | null;
  readonly mentions: PictureMentionDto[];
}

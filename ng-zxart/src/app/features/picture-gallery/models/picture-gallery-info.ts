import {AuthorDto} from '../../../shared/models/author-dto';

/** What the fullscreen gallery shows about a picture, and what its vote controls need. */
export interface PictureGalleryInfo {
  readonly pictureId: number;
  readonly title: string;
  readonly detailsUrl: string;
  readonly authors: readonly AuthorDto[];
  /** Picture format code, named through `picture-format-short.<format>`. */
  readonly format: string;
  readonly votes: number;
  readonly votesAmount: number;
  readonly userVote: number | null;
  readonly denyVoting: boolean;
}

/** A linked entity mentioned by a press article. */
export interface PressMentionDto {
  id: number;
  title: string;
  url: string;
}

/** A tag link on a press article. */
export interface PressTagDto {
  title: string;
  url: string;
}

/** The issue an article was published in, with the rest of its table of contents. */
export interface PressPublicationDto {
  id: number;
  title: string;
  url: string;
  year: number | null;
  imageUrl: string | null;
  articles: PressMentionDto[];
}

/** Rich press-article detail payload from `/press-details/`. */
export interface PressDetailsDto {
  id: number;
  title: string;
  shortTitle: string;
  url: string;
  externalLink: string | null;
  introduction: string | null;
  /** The processed article text, or the archived original while the AI made none. */
  content: string | null;
  tags: PressTagDto[];
  authors: PressMentionDto[];
  people: PressMentionDto[];
  groups: PressMentionDto[];
  software: PressMentionDto[];
  pictures: PressMentionDto[];
  tunes: PressMentionDto[];
  parties: PressMentionDto[];
  publication: PressPublicationDto | null;
}

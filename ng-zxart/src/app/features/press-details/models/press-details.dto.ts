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

/** Rich press-article detail payload from `/press-details/`. */
export interface PressDetailsDto {
  id: number;
  title: string;
  url: string;
  externalLink: string | null;
  introduction: string | null;
  content: string | null;
  tags: PressTagDto[];
  authors: PressMentionDto[];
  people: PressMentionDto[];
  groups: PressMentionDto[];
  software: PressMentionDto[];
  pictures: PressMentionDto[];
  tunes: PressMentionDto[];
  parties: PressMentionDto[];
  publication: PressMentionDto | null;
}

export interface PressArticleAuthorDto {
  id: number;
  title: string;
  url: string;
}

export interface PressArticlePublicationDto {
  id: number;
  title: string;
  url: string;
  year: number | null;
}

export interface PressArticlePreviewDto {
  id: number;
  title: string;
  url: string;
  introduction: string;
  authors: PressArticleAuthorDto[];
  publication: PressArticlePublicationDto | null;
}

export interface PressArticlesPayload {
  articles: PressArticlePreviewDto[];
}

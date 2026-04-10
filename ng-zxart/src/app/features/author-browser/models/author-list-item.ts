export interface AuthorGroup {
  id: number;
  title: string;
  url: string;
}

export interface AuthorListItem {
  id: number;
  url: string;
  entityType: 'author' | 'authorAlias';
  title: string;
  realName: string;
  realNameUrl: string | null;
  groups: AuthorGroup[];
  countryId: number | null;
  countryTitle: string | null;
  countryUrl: string | null;
  cityId: number | null;
  cityTitle: string | null;
  cityUrl: string | null;
  musicRating: number;
  graphicsRating: number;
}

export interface PaginatedAuthorsResponse {
  total: number;
  items: AuthorListItem[];
}

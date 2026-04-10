export interface AuthorFilterOption {
  id: number;
  title: string;
  url: string;
}

export interface AuthorFilterOptions {
  countries: AuthorFilterOption[];
  cities: AuthorFilterOption[];
}

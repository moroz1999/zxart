export interface AuthorFilterOption {
  id: number;
  title: string;
}

export interface AuthorFilterOptions {
  countries: AuthorFilterOption[];
  cities: AuthorFilterOption[];
}

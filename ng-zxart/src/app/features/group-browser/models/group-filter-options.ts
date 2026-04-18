export interface GroupFilterOption {
  id: number;
  title: string;
  url: string;
}

export interface GroupFilterOptions {
  countries: GroupFilterOption[];
  cities: GroupFilterOption[];
}

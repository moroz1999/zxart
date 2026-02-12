export interface RadioFilterCountryOption {
  id: number;
  title: string;
}

export interface RadioFilterCategoryOption {
  id: number;
  title: string;
}

export interface RadioFilterOptionsDto {
  yearRange: {
    min: number | null;
    max: number | null;
  };
  ratingRange: {
    min: number | null;
    max: number | null;
  };
  countries: RadioFilterCountryOption[];
  categories: RadioFilterCategoryOption[];
  formatGroups: string[];
  formats: string[];
  partyOptions: Array<'any' | 'yes' | 'no'>;
}

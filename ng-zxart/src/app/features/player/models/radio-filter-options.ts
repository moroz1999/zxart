export interface RadioFilterCountryOption {
  id: number;
  title: string;
}

export interface RadioFilterOptionsDto {
  yearRange: {
    min: number | null;
    max: number | null;
  };
  countries: RadioFilterCountryOption[];
  formatGroups: string[];
  formats: string[];
  partyOptions: Array<'any' | 'yes' | 'no'>;
}

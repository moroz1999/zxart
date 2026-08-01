export interface PreferenceDto {
  code: string;
  value: string;
}

export type PreferenceValues = Record<string, string>;

export type Theme = 'light' | 'dark';

export interface PreferenceDto {
  code: string;
  value: string | null;
}

export type PreferenceValues = Record<string, string | null>;

export type Theme = 'light' | 'dark';
export type ThemePreference = Theme | null;

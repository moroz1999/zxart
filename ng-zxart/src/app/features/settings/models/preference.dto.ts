export interface PreferenceDto {
  code: string;
  value: string;
}

export type Theme = 'light' | 'dark';

export interface ApiResponse<T> {
  responseStatus: 'success' | 'error';
  responseData?: T;
  errorMessage?: string;
}

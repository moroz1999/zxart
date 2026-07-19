export interface PageMetadataDto {
  title: string;
  description: string;
  noIndex: boolean;
  openGraph: Record<string, string>;
  twitter: Record<string, string>;
  languageLinks: Record<string, string>;
  structuredData: unknown;
}

export interface SearchResultDto {
  title: string;
  url?: string;
  structureType?: string;
}

export interface SearchResultGroup {
  type: string;
  icon: string;
  items: SearchResultDto[];
}

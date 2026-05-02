export interface ProdSummaryDto {
  id: number;
  title: string;
  url: string;
  year: number;
  legalStatus: string;
  legalStatusLabel: string;
  votes: number;
  votesAmount: number;
  imageUrl: string | null;
}

export interface ProdSummariesPayload {
  prods: ProdSummaryDto[];
}

export interface ProdSeriesEntryDto {
  id: number;
  title: string;
  url: string;
  prods: ProdSummaryDto[];
}

export interface ProdSeriesPayload {
  series: ProdSeriesEntryDto[];
}

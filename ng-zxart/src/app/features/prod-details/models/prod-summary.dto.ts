export interface ProdSummaryDto {
  id: number;
  title: string;
  year: number;
  legalStatus: string;
  legalStatusLabel: string;
  votes: number;
  votesAmount: number;
  imageUrl: string | null;
}

export interface ProdSummariesPayload {
  prods: ProdSummaryDto[];
  seriesId: number | null;
}

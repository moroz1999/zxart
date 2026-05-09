export interface ProdGroupRefDto {
  id: number;
  title: string;
}

export interface ProdReleaseInlayDto {
  id: number;
  title: string;
  imageUrl: string | null;
  fullImageUrl: string | null;
  downloadUrl: string;
  releaseTitle: string;
  releaseUrl: string;
  releaseYear: number;
  releaseTypeLabel: string | null;
  releaseBy: ProdGroupRefDto[];
}

export interface ProdReleaseInlaysPayload {
  inlays: ProdReleaseInlayDto[];
}

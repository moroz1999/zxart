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

export type ProdCoverKind = 'inlay' | 'ad';

export interface ProdCoverGroupDto {
  kind: ProdCoverKind;
  items: ProdReleaseInlayDto[];
}

export interface ProdCoversPayload {
  groups: ProdCoverGroupDto[];
}

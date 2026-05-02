export interface ProdFileDto {
  id: number;
  title: string;
  author: string | null;
  fileName: string;
  imageUrl: string | null;
  fullImageUrl: string | null;
  downloadUrl: string;
  isImage: boolean;
}

export interface ProdFilesPayload {
  files: ProdFileDto[];
}

export interface ProdMapsPayload extends ProdFilesPayload {
  mapsUrl?: string;
}

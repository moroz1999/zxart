export interface ProdInstructionFileDto {
  id: number;
  title: string;
  fileName: string;
  downloadUrl: string;
  releaseTitle: string;
  releaseUrl: string;
  releaseYear: number;
  releaseTypeLabel: string | null;
  releaseBy: Array<{id: number; title: string}>;
}

export interface ProdInstructionsPayload {
  files: ProdInstructionFileDto[];
}

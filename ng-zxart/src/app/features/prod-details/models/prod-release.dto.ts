import {
  ProdGroupRefDto,
  ProdHardwareInfoDto,
  ProdLanguageInfoDto,
  ProdLinkInfoDto,
  ProdPartyInfoDto,
} from './prod-core.dto';

export interface ProdReleaseFormatDto {
  format: string;
  label: string;
  emoji: string;
  catalogueUrl: string;
}

export interface ProdReleaseDto {
  id: number;
  title: string;
  url: string;
  year: number;
  version: string;
  releaseType: string;
  releaseTypeLabel: string | null;
  hardwareRequired: string[];
  description: string;
  isRealtime: boolean;
  party: ProdPartyInfoDto | null;
  languages: ProdLanguageInfoDto[];
  hardware: ProdHardwareInfoDto[];
  releaseBy: ProdGroupRefDto[];
  formats: ProdReleaseFormatDto[];
  isDownloadable: boolean;
  isPlayable: boolean;
  downloadUrl: string | null;
  playUrl: string | null;
  fileName: string | null;
  emulatorType: string | null;
  prodLegalStatus: string;
  prodExternalLink: string;
  downloadsCount: number;
  playsCount: number;
  externalLinks: ProdLinkInfoDto[];
}

export interface ProdReleasesPayload {
  releases: ProdReleaseDto[];
}

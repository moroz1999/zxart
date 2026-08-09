import {
  ProdAuthorInfoDto,
  ProdCategoryPathDto,
  ProdGroupRefDto,
  ProdHardwareInfoDto,
  ProdLanguageInfoDto,
  ProdLinkInfoDto,
  ProdPartyInfoDto,
  ProdVotingDto,
} from '../../prod-details/models/prod-core.dto';

import {ProdFileDto} from '../../prod-details/models/prod-file.dto';
import {ProdReleaseFormatDto} from '../../prod-details/models/prod-release.dto';
import {ProdCoverGroupDto} from '../../prod-details/models/prod-release-inlay.dto';
import {ProdInstructionFileDto} from '../../prod-details/models/prod-instruction-file.dto';

export interface ReleaseTabsDto {
  hasScreenshots: boolean;
  hasCovers: boolean;
  hasInstructions: boolean;
  hasStructure: boolean;
  hasPictures: boolean;
}

export interface ReleaseFileStructureItemDto {
  id: number;
  fileName: string;
  size: number;
  type: string;
  typeLabel: string;
  viewable: boolean;
  downloadUrl: string | null;
  items: ReleaseFileStructureItemDto[];
}

export interface ReleaseProdRefDto {
  id: number;
  title: string;
  url: string;
  year: number;
  authorNames: string[];
  thumbnailUrl: string | null;
  categoriesPaths: ProdCategoryPathDto[];
}

export interface ReleaseDetailsDto {
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
  /** The release's own hardware — what it adds or changes. */
  hardware: ProdHardwareInfoDto[];
  /** Inherited from the production, with anything the release repeats removed. */
  prodHardware: ProdHardwareInfoDto[];
  authors: ProdAuthorInfoDto[];
  publishers: ProdGroupRefDto[];
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
  screenshots: ProdFileDto[];
  prod: ReleaseProdRefDto;
  covers: ProdCoverGroupDto[];
  instructions: ProdInstructionFileDto[];
  votes: ProdVotingDto;
  tabs: ReleaseTabsDto;
  fileStructure: ReleaseFileStructureItemDto[];
  canUploadScreenshot: boolean;
  canReorderScreenshots: boolean;
  dateCreated: string;
  submitter: {id: number; userName: string; url: string | null} | null;
}

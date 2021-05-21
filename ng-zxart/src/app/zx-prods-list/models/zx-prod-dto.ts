import {StructureElementDto} from '../../shared/models/structure-element-dto';

export interface ZxProdConnectedItem {
  readonly id: string;
  readonly title: string;
}

export type ZxProdConnectedItems = Array<ZxProdConnectedItem>;

export interface ZxProdConnectedElementDto extends StructureElementDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
}

export type ZxProdConnectedElements = Array<ZxProdConnectedElementDto>;

export interface ZxProdDto extends StructureElementDto {
  readonly title: string;
  readonly year?: string;
  readonly youtubeId?: string;
  readonly listImagesUrls?: Array<string>;
  readonly inlaysUrls?: Array<string>;
  readonly hardwareInfo?: ZxProdConnectedItems;
  readonly groupsInfo?: ZxProdConnectedElements;
  readonly publishersInfo?: ZxProdConnectedElements;
  readonly categoriesInfo?: ZxProdConnectedElements;
  readonly languagesInfo?: ZxProdConnectedItems;
  readonly partyInfo?: ZxProdConnectedElementDto;
  readonly partyPlace?: number;
  readonly votes: number;
  readonly votePercent: number;
}

import {StructureElementDto} from './structure-element-dto';

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

export interface ZxProdAuthorship {
  readonly title: string;
  readonly url: string;
  readonly roles: string[];
}

export type ZxProdConnectedElements = Array<ZxProdConnectedElementDto>;

export interface ZxProdDto extends StructureElementDto {
  readonly title: string;
  readonly dateCreated: number;
  readonly year?: string;
  readonly youtubeId?: string;
  readonly listImagesUrls?: Array<string>;
  readonly inlaysUrls?: Array<string>;
  readonly hardwareInfo?: ZxProdConnectedItems;
  readonly groupsInfo?: ZxProdConnectedElements;
  readonly publishersInfo?: ZxProdConnectedElements;
  readonly authorsInfoShort?: ZxProdAuthorship[];
  readonly categoriesInfo?: ZxProdConnectedElements;
  readonly languagesInfo?: ZxProdConnectedItems;
  readonly partyInfo?: ZxProdConnectedElementDto;
  readonly partyPlace?: number;
  readonly votes: number;
  readonly userVote: number;
  readonly denyVoting?: boolean;
}

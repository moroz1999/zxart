import {StructureElementDto} from '../../shared/models/structure-element-dto';

export interface ZxProdConnectedItem {
  readonly id: string | number;
  readonly title: string;
  readonly url?: string;
}

export type ZxProdConnectedItems = Array<ZxProdConnectedItem>;

export interface ZxProdDto extends StructureElementDto {
  readonly title: string;
  readonly year?: string;
  readonly listImagesUrls?: Array<string>;
  readonly hardwareInfo?: ZxProdConnectedItems;
  readonly groupsInfo?: ZxProdConnectedItems;
  readonly publishersInfo?: ZxProdConnectedItems;
  readonly categoriesInfo?: ZxProdConnectedItems;
  readonly languagesInfo?: ZxProdConnectedItems;
  readonly partyInfo?: ZxProdConnectedItem;
  readonly partyPlace?: number;
  readonly votes: number;
  readonly votePercent: number;
}

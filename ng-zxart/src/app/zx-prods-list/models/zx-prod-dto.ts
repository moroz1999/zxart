import {StructureElementDto} from '../../shared/models/structure-element-dto';

export interface ZxProdDto extends StructureElementDto {
  readonly title: string;
  readonly year?: string;
  readonly listImagesUrls?: Array<string>;
  readonly hardware?: Array<string>;
  readonly groupsTitles?: Array<string>;
  readonly publishersTitles?: Array<string>;
  readonly categoriesTitles?: Array<string>;
  readonly languageTitles?: Array<string>;
  readonly partyTitle?: string;
  readonly partyPlace?: number;
  readonly votes: number;
  readonly votePercent: number;
}

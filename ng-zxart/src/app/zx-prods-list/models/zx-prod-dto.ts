import {StructureElementDto} from '../../shared/models/structure-element-dto';

export interface ZxProdDto extends StructureElementDto {
  readonly title: string;
  readonly imagesUrls: Array<string>;
  readonly votes: number;
  readonly votePercent: number;
}

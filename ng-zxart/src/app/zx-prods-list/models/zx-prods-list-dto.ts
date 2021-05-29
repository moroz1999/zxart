import {ZxProdDto} from '../../shared/models/zx-prod-dto';
import {StructureElementDto} from '../../shared/models/structure-element-dto';

export interface ZxProdsListDto extends StructureElementDto {
  readonly title: string;
  readonly prods?: Array<ZxProdDto>;
  readonly prodsAmount: number;
}

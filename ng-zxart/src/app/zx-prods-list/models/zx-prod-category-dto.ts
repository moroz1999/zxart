import {ZxProdDto} from './zx-prod-dto';
import {StructureElementDto} from '../../shared/models/structure-element-dto';

export interface ZxProdCategoryDto extends StructureElementDto {
  readonly title: string;
  readonly prods: Array<ZxProdDto>;
  readonly prodsAmount: number;
  readonly years: Array<number>;
}

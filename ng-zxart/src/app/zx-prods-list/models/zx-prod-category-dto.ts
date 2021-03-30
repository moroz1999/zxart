import {ZxProdDto} from './zx-prod-dto';
import {StructureElementDto} from '../../shared/models/structure-element-dto';
import {YearSelectorDto} from './year-selector-dto';

export interface ZxProdCategoryDto extends StructureElementDto {
  readonly title: string;
  readonly prods: Array<ZxProdDto>;
  readonly prodsAmount: number;
  readonly yearsSelector: YearSelectorDto;
}

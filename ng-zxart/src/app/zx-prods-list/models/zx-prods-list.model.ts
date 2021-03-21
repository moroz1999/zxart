import {StructureElement} from '../../shared/models/structure-element';
import {ZxProdCategoryResponseDto} from './zx-prod-category-response-dto';
import {ZxProdCategoryDto} from './zx-prod-category-dto';

export class ZxProdsList extends StructureElement {
  public title: string;
  public prodsAmount: number;

  constructor(
    data: ZxProdCategoryResponseDto,
  ) {
    super(data.zxProdCategory);
    this.title = data.zxProdCategory.title;
    this.prodsAmount = data.zxProdCategory.prodsAmount;
  }
}

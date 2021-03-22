import {StructureElement} from '../../shared/models/structure-element';
import {ZxProdCategoryResponseDto} from './zx-prod-category-response-dto';
import {ZxProd} from './zx-prod';

export class ZxProdsList extends StructureElement {
  public title: string;
  public prodsAmount: number;
  public prods: Array<ZxProd>;

  constructor(
    data: ZxProdCategoryResponseDto,
  ) {
    super(data.zxProdCategory);
    this.title = data.zxProdCategory.title;
    this.prods = data.zxProdCategory.prods.map(item => new ZxProd(item));
    this.prodsAmount = data.zxProdCategory.prodsAmount;
  }
}

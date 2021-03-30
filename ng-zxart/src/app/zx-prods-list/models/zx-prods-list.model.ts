import {StructureElement} from '../../shared/models/structure-element';
import {ZxProdCategoryResponseDto} from './zx-prod-category-response-dto';
import {ZxProd} from './zx-prod';
import {SelectorDto} from './selector-dto';

export class ZxProdsList extends StructureElement {
  public h1: string;
  public title: string;
  public prodsAmount: number;
  public prods: Array<ZxProd>;
  public lettersSelector: SelectorDto = [];
  public yearsSelector: SelectorDto = [];
  constructor(
    data: ZxProdCategoryResponseDto,
  ) {
    super(data.zxProdCategory);
    this.h1 = data.zxProdCategory.h1;
    this.title = data.zxProdCategory.title;
    this.prods = data.zxProdCategory.prods.map(item => new ZxProd(item));
    this.prodsAmount = data.zxProdCategory.prodsAmount;
    this.lettersSelector = data.zxProdCategory.lettersSelector;
    this.yearsSelector = data.zxProdCategory.yearsSelector;
  }
}

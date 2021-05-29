import {StructureElement} from '../../shared/models/structure-element';
import {ZxProd} from '../../shared/models/zx-prod';
import {ZxProdsListDto} from './zx-prods-list-dto';

export class ZxProdsList extends StructureElement {
  public title: string;
  public prodsAmount: number;
  public prods: Array<ZxProd> = [];

  constructor(
    data: ZxProdsListDto,
  ) {
    super(data);
    this.title = data.title;
    if (data.prods) {
      this.prods = data.prods.map(item => new ZxProd(item));
    }
    this.prodsAmount = data.prodsAmount;
  }
}

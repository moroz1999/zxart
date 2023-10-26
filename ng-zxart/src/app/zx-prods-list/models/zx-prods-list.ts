import {StructureElement} from '../../shared/models/structure-element';
import {ZxProd} from '../../shared/models/zx-prod';
import {ZxProdsListDto} from './zx-prods-list-dto';

export class ZxProdsList extends StructureElement {
  public title: string;
  public prodsAmount: number;
  public prods: Array<ZxProd> = [];
  public publishedProds: Array<ZxProd> = [];
  public releases: Array<ZxProd> = [];
  public compilations: Array<ZxProd> = [];
  public seriesProds: Array<ZxProd> = [];

  constructor(
    data: ZxProdsListDto,
  ) {
    super(data);
    this.title = data.title;
    if (data.prods) {
      this.prods = data.prods.map(item => new ZxProd(item));
    }
    if (data.publishedProds) {
      this.publishedProds = data.publishedProds.map(item => new ZxProd(item));
    }
    if (data.compilations) {
      this.compilations = data.compilations.map(item => new ZxProd(item));
    }
    if (data.seriesProds) {
      this.seriesProds = data.seriesProds.map(item => new ZxProd(item));
    }

    if (data.releases) {
      this.releases = data.releases.map(item => new ZxProd(item));
    }
    this.prodsAmount = data.prodsAmount ?? 0;
  }
}

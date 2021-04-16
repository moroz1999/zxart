import {StructureElement} from '../../shared/models/structure-element';
import {ZxProdCategoryResponseDto} from './zx-prod-category-response-dto';
import {ZxProd} from './zx-prod';
import {SelectorDto} from './selector-dto';
import {Tag} from '../../shared/models/tag';

export class ZxProdsList extends StructureElement {
  public h1: string;
  public title: string;
  public prodsAmount: number;
  public prods: Array<ZxProd> = [];
  public lettersSelector: SelectorDto = [];
  public yearsSelector: SelectorDto = [];
  public legalStatusesSelector: SelectorDto = [];
  public languagesSelector: SelectorDto = [];
  public formatsSelector: SelectorDto = [];
  public countriesSelector: SelectorDto = [];
  public hardwareSelector: SelectorDto = [];
  public sortingSelector: SelectorDto = [];
  public tagsSelector: Array<Tag> = [];

  constructor(
    data: ZxProdCategoryResponseDto,
  ) {
    super(data.zxProdCategory);
    this.h1 = data.zxProdCategory.h1;
    this.title = data.zxProdCategory.title;
    if (data.zxProdCategory.prods) {
      this.prods = data.zxProdCategory.prods.map(item => new ZxProd(item));
    }
    this.prodsAmount = data.zxProdCategory.prodsAmount;
    this.lettersSelector = data.zxProdCategory.lettersSelector ? data.zxProdCategory.lettersSelector : [];
    this.hardwareSelector = data.zxProdCategory.hardwareSelector? data.zxProdCategory.hardwareSelector : [];
    this.yearsSelector = data.zxProdCategory.yearsSelector? data.zxProdCategory.yearsSelector : [];
    this.legalStatusesSelector = data.zxProdCategory.legalStatusesSelector? data.zxProdCategory.legalStatusesSelector : [];
    this.countriesSelector = data.zxProdCategory.countriesSelector? data.zxProdCategory.countriesSelector : [];
    this.languagesSelector = data.zxProdCategory.languagesSelector? data.zxProdCategory.languagesSelector : [];
    this.formatsSelector = data.zxProdCategory.formatsSelector? data.zxProdCategory.formatsSelector : [];
    this.sortingSelector = data.zxProdCategory.sortingSelector? data.zxProdCategory.sortingSelector : [];
    if (data.zxProdCategory.tagsSelector) {
      this.tagsSelector = data.zxProdCategory.tagsSelector.map(item => new Tag(item));
    }
  }
}

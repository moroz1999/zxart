import {StructureElement} from '../../../shared/models/structure-element';
import {ZxProd} from '../../../shared/models/zx-prod';
import {SelectorDto} from './selector-dto';
import {Tag} from '../../../shared/models/tag';
import {ZxProdCategoryDto} from './zx-prod-category-dto';
import {SelectorValues} from './selector-values';
import {CategoriesSelectorDto} from './categories-selector-dto';

export class ZxProdCategory extends StructureElement {
    public h1: string;
    public title: string;
    public prodsAmount: number;
    public prods: Array<ZxProd> = [];
    public categoriesSelector: CategoriesSelectorDto = [];
    public lettersSelector: SelectorDto = [];
    public yearsSelector: SelectorDto = [];
    public legalStatusesSelector: SelectorDto = [];
    public releaseTypesSelector: SelectorDto = [];
    public languagesSelector: SelectorDto = [];
    public formatsSelector: SelectorDto = [];
    public countriesSelector: SelectorDto = [];
    public hardwareSelector: SelectorDto = [];
    public sortingSelector: SelectorDto = [];
    public tagsSelector: Array<Tag> = [];
    public selectorValues: SelectorValues;

    constructor(
        data: ZxProdCategoryDto,
    ) {
        super(data);
        this.h1 = data.h1;
        this.title = data.title;
        if (data.prods) {
            this.prods = data.prods.map(item => new ZxProd(item));
        }
        this.prodsAmount = data.prodsAmount;
        this.categoriesSelector = data.categoriesSelector ? data.categoriesSelector : [];
        this.lettersSelector = data.lettersSelector ? data.lettersSelector : [];
        this.hardwareSelector = data.hardwareSelector ? data.hardwareSelector : [];
        this.yearsSelector = data.yearsSelector ? data.yearsSelector : [];
        this.legalStatusesSelector = data.legalStatusesSelector ? data.legalStatusesSelector : [];
        this.releaseTypesSelector = data.releaseTypesSelector ? data.releaseTypesSelector : [];
        this.countriesSelector = data.countriesSelector ? data.countriesSelector : [];
        this.languagesSelector = data.languagesSelector ? data.languagesSelector : [];
        this.formatsSelector = data.formatsSelector ? data.formatsSelector : [];
        this.sortingSelector = data.sortingSelector ? data.sortingSelector : [];
        if (data.tagsSelector) {
            this.tagsSelector = data.tagsSelector.map(item => new Tag(item));
        }
        this.selectorValues = data.selectorValues;
    }
}

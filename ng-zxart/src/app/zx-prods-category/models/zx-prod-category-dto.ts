import {ZxProdDto} from '../../shared/models/zx-prod-dto';
import {StructureElementDto} from '../../shared/models/structure-element-dto';
import {SelectorDto} from './selector-dto';
import {TagDto} from '../../shared/models/tag-dto';
import {CategoriesSelectorDto} from '../../categories-selector-dto';
import {SelectorValues} from './selector-values';

export interface ZxProdCategoryDto extends StructureElementDto {
  readonly h1: string;
  readonly title: string;
  readonly prods?: Array<ZxProdDto>;
  readonly prodsAmount: number;
  readonly categoriesSelector?: CategoriesSelectorDto;
  readonly lettersSelector?: SelectorDto;
  readonly yearsSelector?: SelectorDto;
  readonly countriesSelector?: SelectorDto;
  readonly languagesSelector?: SelectorDto;
  readonly legalStatusesSelector?: SelectorDto;
  readonly formatsSelector?: SelectorDto;
  readonly hardwareSelector?: SelectorDto;
  readonly sortingSelector?: SelectorDto;
  readonly tagsSelector?: Array<TagDto>;
  readonly selectorValues: SelectorValues;
}

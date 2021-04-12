import {ZxProdDto} from './zx-prod-dto';
import {StructureElementDto} from '../../shared/models/structure-element-dto';
import {SelectorDto} from './selector-dto';
import {TagDto} from '../../shared/models/tag-dto';

export interface ZxProdCategoryDto extends StructureElementDto {
  readonly h1: string;
  readonly title: string;
  readonly prods?: Array<ZxProdDto>;
  readonly prodsAmount: number;
  readonly lettersSelector: SelectorDto;
  readonly yearsSelector: SelectorDto;
  readonly hardwareSelector: SelectorDto;
  readonly sortingSelector: SelectorDto;
  readonly tagsSelector: Array<TagDto>;
}

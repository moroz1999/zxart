import {ZxProdDto} from './zx-prod-dto';

export interface ZxProdCategoryDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly prods: Array<ZxProdDto>;
}

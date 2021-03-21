import {StructureElement} from '../../shared/models/structure-element';
import {ZxProdCategoryResponseDto} from './zx-prod-category-response-dto';

export class ZxProdsList implements StructureElement {
  public id: number;
  public title: string;

  constructor(
    dto: ZxProdCategoryResponseDto,
  ) {
    this.id = dto.zxProdCategory.id;
    this.title = dto.zxProdCategory.title;
  }
}

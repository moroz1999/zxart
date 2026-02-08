import {ZxProdDto} from '../../../shared/models/zx-prod-dto';
import {StructureElementDto} from '../../../shared/models/structure-element-dto';

export interface ZxProdsListDto extends StructureElementDto {
    readonly title: string;
    readonly prods?: Array<ZxProdDto>;
    readonly publishedProds?: Array<ZxProdDto>;
    readonly releases?: Array<ZxProdDto>;
    readonly prodsAmount: number;
    readonly compilations?: Array<ZxProdDto>;
    readonly seriesProds?: Array<ZxProdDto>;
}

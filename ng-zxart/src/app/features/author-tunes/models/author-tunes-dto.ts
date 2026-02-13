import {StructureElementDto} from '../../../shared/models/structure-element-dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

export interface AuthorTunesYearDto {
  readonly year: number;
  readonly tunes: ZxTuneDto[];
}

export interface AuthorTunesDto extends StructureElementDto {
  readonly tunesByYear: AuthorTunesYearDto[];
}

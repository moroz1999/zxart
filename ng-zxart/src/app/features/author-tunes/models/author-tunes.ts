import {StructureElement} from '../../../shared/models/structure-element';
import {AuthorTunesDto, AuthorTunesYearDto} from './author-tunes-dto';

export class AuthorTunes extends StructureElement {
  public readonly tunesByYear: AuthorTunesYearDto[];

  constructor(dto: AuthorTunesDto) {
    super(dto);
    this.tunesByYear = dto.tunesByYear ?? [];
  }
}

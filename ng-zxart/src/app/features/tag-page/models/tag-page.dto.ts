import {PageMetadataDto} from '../../../shared/models/page-metadata.dto';

export type TagPageSection = 'graphics' | 'music' | 'software';

export interface TagPageDto {
  readonly id: number;
  readonly section: TagPageSection;
  readonly title: string;
  readonly heading: string;
  readonly metadata: PageMetadataDto;
}

import {TagItem} from '../../../shared/models/tag-item';

export interface TagsPayloadDto {
  readonly elementId: number;
  readonly tags: TagItem[];
  readonly suggestedTags: TagItem[];
}

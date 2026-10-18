import {Tag} from '../models/tag';
import {TagItem} from '../models/tag-item';

/**
 * A tag search result as a pickable item. The search title is decorated with the
 * tag's synonyms and description, so the item is named by the tag's own name —
 * the value forms save and searches filter by.
 */
export function tagToItem(tag: Tag): TagItem {
  return {
    id: Number.isFinite(tag.id) ? tag.id : null,
    title: tag.value,
    description: tag.description || null,
  };
}

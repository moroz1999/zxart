/** Groups of items a production can be split into a new production by. */
export type ProdSplitGroupKey =
  | 'properties'
  | 'authors'
  | 'publishers'
  | 'groups'
  | 'releases'
  | 'screenshots'
  | 'links';

/**
 * One splittable item. `key` is what the split action reads the checked item
 * under, so it is an authorship record for an author, an element id for a
 * related entity or a screenshot, and `origin;id` for an external link.
 */
export interface ProdSplitItemDto {
  key: string;
  title: string;
  /** Page of the item: an SPA route for entities, an external address for links. */
  url: string | null;
  /** Thumbnail of a screenshot. */
  imageUrl: string | null;
}

export interface ProdSplitGroupDto {
  group: ProdSplitGroupKey;
  items: ProdSplitItemDto[];
}

export interface ProdSplitDataDto {
  id: number;
  title: string;
  groups: ProdSplitGroupDto[];
  /** Present when the service recovered from a failed request. */
  errorMessage?: string;
}

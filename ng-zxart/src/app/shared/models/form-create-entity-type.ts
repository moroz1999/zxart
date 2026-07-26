/**
 * Entity kinds that `/formdata/` can open a creation form for (and create),
 * mirroring the backend `ZxArt\Forms\FormCreateType` enum.
 */
export type FormCreateEntityType =
  | 'author'
  | 'group'
  | 'party'
  | 'prodBatch'
  | 'pictureBatch'
  | 'musicBatch'
  | 'release';

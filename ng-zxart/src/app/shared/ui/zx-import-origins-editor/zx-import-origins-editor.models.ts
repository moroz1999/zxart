/**
 * One id an entity carries on an external portal. A type alias, not an
 * interface: the rows travel as form field values, which are index-signature
 * typed.
 */
export type ImportOriginItem = {
  /** Portal code, one of the options the backend offers. */
  origin: string;
  /** Id the entity has on that portal. */
  importId: string;
};

/**
 * What the editor holds, indexed by row. The host sends it as the
 * `importOrigins` form field; rows with an empty id are dropped on save.
 */
export type ImportOriginFields = Record<string, ImportOriginItem>;

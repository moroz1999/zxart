/** A current member of a group/entity with editable role and active period. */
export interface MemberRoleItem {
  id: number;
  title: string;
  startDate: string;
  endDate: string;
  roles: string[];
}

/**
 * The legacy `publicReceive` member fields produced by the editor:
 * `addAuthor` is the id of a member being added (or ''), the maps are keyed by
 * existing author id plus `'new'` for the member being added.
 */
export interface MemberFields {
  addAuthor: string;
  addAuthorRole: Record<string, string[]>;
  addAuthorStartDate: Record<string, string>;
  addAuthorEndDate: Record<string, string>;
}

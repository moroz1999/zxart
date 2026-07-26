/** A current member of a group/entity with editable role and active period. */
export interface MemberRoleItem {
  id: number;
  title: string;
  startDate: string;
  endDate: string;
  roles: string[];
}

/** Per-author fields submitted by the editor, keyed by author id. */
export interface MemberFields {
  addAuthorRole: Record<string, string[]>;
  addAuthorStartDate: Record<string, string>;
  addAuthorEndDate: Record<string, string>;
}

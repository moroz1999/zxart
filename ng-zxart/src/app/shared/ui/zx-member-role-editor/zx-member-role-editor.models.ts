/** A current member of a group/entity with editable role and active period. */
export interface MemberRoleItem {
  id: number;
  title: string;
  startDate: string;
  endDate: string;
  roles: string[];
}

/**
 * What the editor holds, keyed by member id. The host names the backend fields
 * these go into: a group form submits the roles of its authors, an author form
 * the roles it holds in its groups.
 */
export interface MemberFields {
  roles: Record<string, string[]>;
  startDates: Record<string, string>;
  endDates: Record<string, string>;
}

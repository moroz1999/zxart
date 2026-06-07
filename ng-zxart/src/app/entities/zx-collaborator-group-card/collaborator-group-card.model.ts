/**
 * Data for a collaborator group card. Used by the author "Worked with" groups and the group
 * "Connections" published groups.
 */
export interface CollaboratorGroupCardData {
  title: string;
  url: string;
  years: string | null;
  membersCount: number;
  jointProds: number;
}

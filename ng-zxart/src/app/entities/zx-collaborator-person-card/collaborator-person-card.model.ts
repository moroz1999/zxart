/**
 * Data for a collaborator person card. Both the author "Worked with" people and the group
 * "Connections" people map onto this shape; the stat breakdown and roles are optional so each
 * feature can supply whichever it has.
 */
export interface CollaboratorPersonCardData {
  title: string;
  url: string;
  jointTotal: number;
  roles?: string[];
  jointPictures?: number;
  jointTunes?: number;
  jointProds?: number;
}

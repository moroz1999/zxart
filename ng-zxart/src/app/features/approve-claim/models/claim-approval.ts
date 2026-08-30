/** The authorship claim a moderator is asked to approve. */
export interface ClaimApproval {
  authorId: number;
  authorTitle: string;
  /** Routed SPA URL of the claimed author. */
  authorUrl: string;
  userId: number;
  userName: string;
  /** Whether the current visitor holds the privilege to approve it. */
  canApprove: boolean;
  /** Whether the account already holds this author. */
  approved: boolean;
}

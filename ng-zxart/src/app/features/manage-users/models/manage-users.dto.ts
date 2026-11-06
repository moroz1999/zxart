/** One row of the management users list. Dates are Unix timestamps. */
export interface ManageUserListItemDto {
  id: number;
  userName: string;
  email: string;
  dateCreated: number;
  verified: boolean;
  banned: boolean;
  groupIds: number[];
}

export interface ManageUsersListDto {
  total: number;
  pageSize: number;
  users: ManageUserListItemDto[];
}

export interface ManageUserDetailsDto {
  id: number;
  userName: string;
  email: string;
  dateCreated: number;
  verified: boolean;
  banned: boolean;
  commentsAllowed: boolean;
  showEmail: boolean;
  vip: boolean;
  supporter: boolean;
  volunteer: boolean;
  /** 0 when the user claims no author. */
  authorId: number;
  authorTitle: string;
  groupIds: number[];
  commentsCount: number;
  votesCount: number;
}

/** The management form of a user; the group list replaces the user's groups. */
export interface ManageUserSaveRequest {
  userName: string;
  email: string;
  verified: boolean;
  commentsAllowed: boolean;
  showEmail: boolean;
  vip: boolean;
  supporter: boolean;
  volunteer: boolean;
  groupIds: number[];
}

export interface ManageUserGroupDto {
  id: number;
  groupName: string;
  description: string;
  membersCount: number;
}

export interface ManageUserGroupSaveRequest {
  groupName: string;
  description: string;
}

export interface ManageMonthlyCountDto {
  /** Y-m */
  month: string;
  count: number;
}

export interface ManageUsersStatsDto {
  total: number;
  newLastWeek: number;
  newLastMonth: number;
  banned: number;
  unverified: number;
  /** The last twelve months, oldest first. */
  registrationsByMonth: ManageMonthlyCountDto[];
}

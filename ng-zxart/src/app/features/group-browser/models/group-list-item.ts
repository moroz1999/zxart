export interface GroupListItem {
  id: number;
  url: string;
  entityType: 'group' | 'groupAlias';
  title: string;
  groupType: string;
  realGroupTitle: string | null;
  realGroupUrl: string | null;
  countryId: number | null;
  countryTitle: string | null;
  countryUrl: string | null;
  cityId: number | null;
  cityTitle: string | null;
  cityUrl: string | null;
}

export interface PaginatedGroupsResponse {
  total: number;
  items: GroupListItem[];
}

import {PressArticlePreviewDto} from '../../prod-details/models/press-article.dto';

export interface GroupLocationItemDto {
  title: string;
  url: string;
}

export interface GroupLocationDto {
  city: GroupLocationItemDto | null;
  country: GroupLocationItemDto | null;
}

export interface GroupLinkDto {
  url: string;
  label: string;
}

export interface GroupRefDto {
  id: number;
  title: string;
  url: string;
  years: string | null;
}

export interface GroupSubgroupDto {
  id: number;
  title: string;
  abbreviation: string;
  url: string;
  membersCount: number;
  prodsCount: number;
  years: string | null;
}

export interface GroupMemberDto {
  id: number;
  title: string;
  url: string;
  realName: string;
  roles: string[];
  years: string | null;
  subgroups: string[];
}

export interface GroupCountersDto {
  members: number;
  subgroups: number;
  prods: number;
  published: number;
  releases: number;
  mentions: number;
  comments: number;
}

export interface GroupTabsDto {
  hasProds: boolean;
  hasPublished: boolean;
  hasReleases: boolean;
  hasMembers: boolean;
  hasSubgroups: boolean;
  hasConnections: boolean;
  hasMentions: boolean;
}

export interface GroupBreadcrumbDto {
  title: string;
  url: string;
}

export interface GroupCoreDto {
  id: number;
  entityType: 'group' | 'groupAlias';
  title: string;
  abbreviation: string;
  url: string;
  type: string;
  slogan: string;
  imageUrl: string;
  years: string | null;
  nature: string[];
  location: GroupLocationDto;
  links: GroupLinkDto[];
  parentGroups: GroupRefDto[];
  aliases: GroupRefDto[];
  subgroups: GroupSubgroupDto[];
  members: GroupMemberDto[];
  mentions: PressArticlePreviewDto[];
  counters: GroupCountersDto;
  tabs: GroupTabsDto;
  breadcrumbs: GroupBreadcrumbDto[];
}

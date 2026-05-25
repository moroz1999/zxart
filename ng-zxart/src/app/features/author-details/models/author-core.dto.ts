export interface AuthorLocationItemDto {
  title: string;
  url: string;
}

export interface AuthorGroupDto {
  id: number;
  title: string;
  url: string;
  years: string | null;
}

export interface AuthorAliasRefDto {
  id: number;
  title: string;
  url: string;
}

export interface AuthorLinkDto {
  url: string;
  label: string;
}

export interface AuthorTechDto {
  palette: string;
  ayChip: string;
  ayChannels: string;
  ayClock: string;
  intFreq: string;
}

export interface AuthorCountersDto {
  pictures: number;
  tunes: number;
  prods: number;
  comments: number;
}

export interface AuthorRatingsDto {
  artist: number;
  musician: number;
}

export interface AuthorTabsDto {
  hasPictures: boolean;
  hasTunes: boolean;
  hasProds: boolean;
}

export interface AuthorCoreDto {
  id: number;
  entityType: 'author' | 'authorAlias';
  title: string;
  realName: string;
  url: string;
  parentUrl: string | null;
  parentTitle: string | null;
  primaryAuthor: AuthorAliasRefDto | null;
  siteUser: string | null;
  joined: string | null;
  roles: string[];
  badges: string[];
  location: AuthorLocationItemDto[];
  groups: AuthorGroupDto[];
  aliases: AuthorAliasRefDto[];
  links: AuthorLinkDto[];
  tech: AuthorTechDto;
  counters: AuthorCountersDto;
  ratings: AuthorRatingsDto;
  tabs: AuthorTabsDto;
}

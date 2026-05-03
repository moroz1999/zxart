export interface ProdCategoryRefDto {
  id: number;
  title: string;
  url: string;
}

export interface ProdCategoryPathDto {
  categories: ProdCategoryRefDto[];
}

export interface ProdLanguageInfoDto {
  code: string;
  title: string;
  emoji: string;
  catalogueUrl: string;
}

export interface ProdHardwareInfoDto {
  id: string;
  title: string;
}

export interface ProdLinkInfoDto {
  url: string;
  name: string;
  image: string;
}

export interface ProdPartyInfoDto {
  id: number;
  title: string;
  abbreviation: string | null;
  url: string;
  place: number | null;
  compoLabel: string | null;
}

export interface ProdAuthorInfoDto {
  id: number;
  title: string;
  url: string;
  roles: string[];
}

export interface ProdGroupRefDto {
  id: number;
  title: string;
  url: string;
}

export interface ProdTagRefDto {
  id: number;
  title: string;
  url: string;
}

export interface ProdVotingDto {
  votes: number;
  votesAmount: number;
  userVote: number | null;
  denyVoting: boolean;
  votePercent: number | null;
}

export interface ProdSubmitterDto {
  id: number;
  userName: string;
  url: string;
}

export interface ProdCoreDto {
  elementId: number;
  title: string;
  altTitle: string;
  prodUrl: string;
  h1: string;
  metaTitle: string;
  year: number;
  legalStatus: string;
  legalStatusLabel: string;
  externalLink: string;
  youtubeId: string;
  description: string;
  htmlDescription: boolean;
  instructions: string;
  generatedDescription: string;
  dateCreated: string;
  catalogueYearUrl: string;
  categoriesPaths: ProdCategoryPathDto[];
  languages: ProdLanguageInfoDto[];
  hardware: ProdHardwareInfoDto[];
  links: ProdLinkInfoDto[];
  party: ProdPartyInfoDto | null;
  authors: ProdAuthorInfoDto[];
  publishers: ProdGroupRefDto[];
  groups: ProdGroupRefDto[];
  tags: ProdTagRefDto[];
  voting: ProdVotingDto;
  submitter: ProdSubmitterDto | null;
}

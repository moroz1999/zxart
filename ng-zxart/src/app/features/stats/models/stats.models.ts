import {CommentAuthorDto} from '../../comments/models/comment.dto';

export interface StatsOverview {
  prods: number;
  releases: number;
  authors: number;
  authorsWithAliases: number;
  groups: number;
  groupsWithAliases: number;
  music: number;
  pictures: number;
}

export interface StatsYearSeries {
  years: number[];
  all: number[];
  rated: number[];
}

export interface StatsDistribution {
  titleKey: string;
  classes: string[];
  rows: number[][];
}

export interface StatsDailySeries {
  labelKey: string;
  dates: string[];
  data: number[];
}

export interface StatsTopUser extends CommentAuthorDto {
  count: number;
}

export interface StatsCategorySummary {
  totalWorks: number;
  peakYear: number;
  dailyTotal: number;
}

export interface StatsDistributionsSection {
  years: number[];
  distributions: StatsDistribution[];
}

export interface StatsDistributionBlock {
  years: number[];
  distribution: StatsDistribution;
}

export interface StatsTopUsersSection {
  unitKey: string;
  users: StatsTopUser[];
}

export interface StatsCategorySection {
  totalWorks: number;
  peakYear: number;
  dailyTotal: number;
  topUnitKey: string;
  series: StatsYearSeries;
  distributions: StatsDistribution[];
  daily: StatsDailySeries;
  top: StatsTopUser[];
}

export interface StatsUsersSection {
  voters: StatsTopUser[];
  comments: StatsTopUser[];
  tags: StatsTopUser[];
}

export type StatsCategoryKey = 'soft' | 'music' | 'gfx';

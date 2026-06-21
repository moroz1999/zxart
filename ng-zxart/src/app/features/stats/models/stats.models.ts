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
  avg: number[];
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

export interface StatsTopUser {
  name: string;
  url: string | null;
  badge: string | null;
  count: number;
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

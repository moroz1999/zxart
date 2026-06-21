export interface GeoCounters {
  authors: number;
  groups: number;
  parties: number;
}

export interface GeoCity {
  id: number;
  countryId: number;
  title: string;
  url: string;
  latitude: number;
  longitude: number;
  counters: GeoCounters;
}

export interface GeoCountry {
  id: number;
  title: string;
  url: string;
  latitude: number;
  longitude: number;
  counters: GeoCounters;
  cities: GeoCity[];
}

export interface GeoMapResponse {
  countries: GeoCountry[];
  counters: GeoCounters;
}

export interface GeoListResponse<T> {
  total: number;
  items: T[];
}

export interface GeoBounds {
  north: number;
  south: number;
  east: number;
  west: number;
}

export interface GeoAuthorItem {
  id: number;
  url: string;
  title: string;
  realName: string;
  cityTitle: string | null;
  countryTitle: string | null;
  musicRating: number;
  graphicsRating: number;
}

export interface GeoGroupItem {
  id: number;
  url: string;
  title: string;
  groupType: string;
  cityTitle: string | null;
  countryTitle: string | null;
}

export interface GeoPartyItem {
  id: number;
  title: string;
  url: string;
  cityTitle: string | null;
  countryTitle: string | null;
  entries: number;
}

export type GeoEntityType = 'authors' | 'groups' | 'parties';
export type GeoFilter = {kind: 'country'; country: GeoCountry} | {kind: 'city'; city: GeoCity};

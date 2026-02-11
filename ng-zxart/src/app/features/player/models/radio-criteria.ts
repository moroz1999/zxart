export interface RadioCriteria {
  minRating: number | null;
  maxRating: number | null;
  yearsInclude: number[];
  yearsExclude: number[];
  countriesInclude: number[];
  countriesExclude: number[];
  formatGroupsInclude: string[];
  formatGroupsExclude: string[];
  formatsInclude: string[];
  formatsExclude: string[];
  prodCategoriesInclude: number[];
  bestVotesLimit: number | null;
  maxPlays: number | null;
  minPartyPlace: number | null;
  requireGame: boolean | null;
  hasParty: boolean | null;
  notVotedByUserId: number | null;
}

export const EMPTY_RADIO_CRITERIA: RadioCriteria = {
  minRating: null,
  maxRating: null,
  yearsInclude: [],
  yearsExclude: [],
  countriesInclude: [],
  countriesExclude: [],
  formatGroupsInclude: [],
  formatGroupsExclude: [],
  formatsInclude: [],
  formatsExclude: [],
  prodCategoriesInclude: [],
  bestVotesLimit: null,
  maxPlays: null,
  minPartyPlace: null,
  requireGame: null,
  hasParty: null,
  notVotedByUserId: null,
};

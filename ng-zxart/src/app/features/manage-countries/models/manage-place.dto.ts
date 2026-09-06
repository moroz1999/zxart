import {FormLanguage} from '../../../shared/models/form-data-response';

/** One country of the management list. */
export interface ManageCountryDto {
  id: number;
  /** Title in the interface language of the request. */
  title: string;
  /** Titles keyed by language id, as the edit form takes them. */
  titles: Record<string, string>;
  latitude: number;
  longitude: number;
  /** Authors, groups and parties that name this place. */
  usages: number;
  cities: number;
}

/** One city of the management list, with the country it belongs to. */
export interface ManageCityDto {
  id: number;
  countryId: number;
  title: string;
  titles: Record<string, string>;
  latitude: number;
  longitude: number;
  usages: number;
}

/** Both lists plus the languages their titles are edited in. */
export interface ManagePlacesDto {
  languages: FormLanguage[];
  countries: ManageCountryDto[];
  cities: ManageCityDto[];
}

/** Body of a country create or update request. */
export interface CountrySaveRequest {
  id?: number;
  titles: Record<string, string>;
  latitude: number;
  longitude: number;
}

/** Body of a city create or update request. */
export interface CitySaveRequest {
  id?: number;
  /**
   * The country the city belongs to, required on a creation and on an update
   * alike. An update naming another country moves the city there.
   */
  countryId: number;
  titles: Record<string, string>;
  latitude: number;
  longitude: number;
}

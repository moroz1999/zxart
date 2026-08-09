/** Labels of one hardware item in one interface language. */
export interface HardwareNameDto {
  language: string;
  name: string;
  shortName: string;
}

/** One item of the editable hardware catalog. */
export interface HardwareItemDto {
  id: number;
  code: string;
  category: string;
  position: number;
  /** Keyed by two-letter language code. */
  names: Record<string, HardwareNameDto>;
  /** How many releases and productions reference it; deletion is refused above 0. */
  usages: number;
}

/** The whole catalog plus the option lists the management form needs. */
export interface HardwareCatalogDto {
  languages: string[];
  categories: string[];
  items: HardwareItemDto[];
}

/** Body of a create or update request. */
export interface HardwareSaveRequest {
  id?: number;
  code: string;
  category: string;
  position: number;
  names: Record<string, {name: string; shortName: string}>;
}

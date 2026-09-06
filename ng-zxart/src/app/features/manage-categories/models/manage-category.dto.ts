import {FormLanguage} from '../../../shared/models/form-data-response';

/** One category of the management tree, in depth-first order. */
export interface ManageCategoryDto {
  id: number;
  /** `null` for a top-level category. */
  parentId: number | null;
  level: number;
  /** Title in the interface language of the request. */
  title: string;
  /** Titles keyed by language id, as the edit form takes them. */
  titles: Record<string, string>;
  /** Productions filed under this category itself. */
  prods: number;
  /** The same, plus everything filed in its subcategories. */
  prodsTotal: number;
  subCategories: number;
}

/** The whole tree plus the languages its titles are edited in. */
export interface ManageCategoriesDto {
  languages: FormLanguage[];
  categories: ManageCategoryDto[];
}

/** Body of a create or update request. */
export interface CategorySaveRequest {
  id?: number;
  /**
   * Where the category belongs, on a creation and on an update alike; `null`
   * is the top level. An update naming another category moves it there.
   */
  parentId: number | null;
  titles: Record<string, string>;
}

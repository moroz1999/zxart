export interface CategorySelectorDto {
  name: string;
  children?: CategorySelectorDto[];
}

export type CategoriesSelectorDto = Array<CategorySelectorDto>;

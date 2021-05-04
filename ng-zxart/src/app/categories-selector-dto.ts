export interface CategorySelectorDto {
  id: number;
  name: string;
  url: string;
  children?: CategorySelectorDto[];
}

export type CategoriesSelectorDto = Array<CategorySelectorDto>;

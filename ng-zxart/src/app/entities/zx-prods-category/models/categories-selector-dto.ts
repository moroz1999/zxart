export interface CategorySelectorDto {
    id: number;
    name: string;
    selected: boolean;
    children?: CategorySelectorDto[];
}

export type CategoriesSelectorDto = Array<CategorySelectorDto>;

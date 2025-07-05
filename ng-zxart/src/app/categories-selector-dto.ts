export interface CategorySelectorDto {
    id: number;
    name: string;
    url: string;
    selected: boolean;
    children?: CategorySelectorDto[];
}

export type CategoriesSelectorDto = Array<CategorySelectorDto>;

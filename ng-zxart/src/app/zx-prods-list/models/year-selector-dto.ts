export interface YearSelectorValue {
  readonly value: number;
  readonly title: string;
  readonly selected: boolean;
}

export type YearSelectorDto = Array<YearSelectorValue>;

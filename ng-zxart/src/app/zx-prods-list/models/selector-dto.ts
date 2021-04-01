export interface SelectorValue {
  readonly value: string;
  readonly title: string;
  readonly selected: boolean;
}

export type SelectorDto = Array<SelectorValue>;

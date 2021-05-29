export interface SelectorValue {
  readonly value: string;
  readonly title: string;
  readonly selected: boolean;
}

interface SelectorGroup {
  readonly title: string;
  readonly values: Array<SelectorValue>;
}

export type SelectorDto = Array<SelectorGroup>;

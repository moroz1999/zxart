export interface SelectorValue {
  readonly value: Primitive;
  readonly title: string;
  readonly selected: boolean;
}

export type SelectorDto = Array<SelectorValue>;

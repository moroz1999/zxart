export interface ChipItem {
  /** Optional stable key so consumers can remove by id/value (falls back to reference identity). */
  readonly id?: string | number | null;
  readonly title: string;
  readonly url?: string;
}

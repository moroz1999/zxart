/** Minimal reference to the prod/release an item (picture, tune) originates from. */
export interface ProdContextDto {
  readonly title: string;
  readonly url: string;
  readonly year?: string | null;
}

/** A linked place (country or city) shown next to a party. */
export interface PartyLocationDto {
  readonly title: string;
  readonly url: string;
}

export interface PartyDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly year: string | null;
  readonly imageUrl: string;
  /** Present in the party collections (`/parties-data/`, homepage); search results omit it. */
  readonly country?: PartyLocationDto | null;
  readonly city?: PartyLocationDto | null;
}

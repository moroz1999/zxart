export interface ZxProdDto {
  readonly id: number;
  readonly title: string;
  readonly url: string;
  readonly imagesUrls: Array<string>;
  readonly votes: number;
  readonly votePercent: number;
}

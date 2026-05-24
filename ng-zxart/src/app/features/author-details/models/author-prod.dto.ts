export interface AuthorProdCoAuthorDto {
  name: string;
  url: string;
}

export interface AuthorProdDto {
  id: number;
  title: string;
  url: string;
  year: number;
  thumbnailUrl: string | null;
  category: string;
  votes: number;
  votesAmount: number;
  rolesInProd: string[];
  coAuthors: AuthorProdCoAuthorDto[];
  type: 'prod' | 'release';
}

export interface CommentAuthorDto {
  name: string;
  url?: string;
  badge?: string;
}

export interface CommentDto {
  id: number;
  author: CommentAuthorDto;
  date: string;
  content: string;
  parentId?: number;
  children: CommentDto[];
}

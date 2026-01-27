export interface CommentAuthorDto {
  name: string;
  url?: string;
  badges: string[];
}

export interface CommentTargetDto {
  title: string;
  url: string;
}

export interface CommentDto {
  id: number;
  author: CommentAuthorDto;
  date: string;
  content: string;
  canEdit: boolean;
  canDelete: boolean;
  target?: CommentTargetDto;
  parentId?: number;
  children: CommentDto[];
}

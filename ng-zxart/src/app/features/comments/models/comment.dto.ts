export interface CommentAuthorDto {
  name: string;
  url?: string;
  badges: string[];
}

export interface CommentTargetDto {
  title: string;
  url: string;
  type: string;
  imageUrl?: string;
  authorName?: string;
}

export interface CommentDto {
  id: number;
  author: CommentAuthorDto;
  date: string;
  content: string;
  originalContent: string;
  canEdit: boolean;
  canDelete: boolean;
  target?: CommentTargetDto;
  parentId?: number;
  children: CommentDto[];
}

export interface CommentsListDto {
  comments: CommentDto[];
  currentPage: number;
  pagesAmount: number;
  totalCount: number;
}

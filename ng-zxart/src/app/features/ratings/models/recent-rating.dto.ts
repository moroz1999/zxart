import {CommentAuthorDto} from '../../comments/models/comment.dto';

export interface RecentRatingDto {
  user: CommentAuthorDto;
  rating: string;
  targetTitle: string;
  targetUrl: string;
}

export interface RecentRatingsListDto {
  items: RecentRatingDto[];
  hasMore: boolean;
}

export interface AuthorRatingsListDto {
  items: RecentRatingDto[];
  currentPage: number;
  pagesAmount: number;
  totalCount: number;
}

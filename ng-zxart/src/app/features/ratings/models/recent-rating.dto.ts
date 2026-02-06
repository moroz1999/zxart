import {CommentAuthorDto} from '../../comments/models/comment.dto';

export interface RecentRatingDto {
  user: CommentAuthorDto;
  rating: string;
  targetTitle: string;
  targetUrl: string;
}

export interface RecentRatingsListDto {
  items: RecentRatingDto[];
}

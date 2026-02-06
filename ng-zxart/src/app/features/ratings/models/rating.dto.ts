import {CommentAuthorDto} from '../../comments/models/comment.dto';

export interface RatingDto {
  user: CommentAuthorDto;
  rating: string;
  date: string;
}

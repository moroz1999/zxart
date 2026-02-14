import {CommentDto} from './comment.dto';

export type CommentChangeType = 'reply' | 'edit' | 'delete';

export interface CommentChangeEvent {
  type: CommentChangeType;
  comment: CommentDto;
}
import {CommonModule} from '@angular/common';
import {Component, EventEmitter, Input, Output} from '@angular/core';
import {CommentDto} from '../../models/comment.dto';
import {CommentsService} from '../../services/comments.service';
import {CommentFormComponent} from '../comment-form/comment-form.component';
import {CommentAuthorComponent} from '../comment-author/comment-author.component';

@Component({
  selector: 'app-comment',
  standalone: true,
    imports: [CommonModule, CommentFormComponent, CommentAuthorComponent],
  templateUrl: './comment.component.html',
  styleUrls: ['./comment.component.scss']
})
export class CommentComponent {
  @Input() comment!: CommentDto;
  @Output() commentChanged = new EventEmitter<void>();

  showReplyForm = false;
  showEditForm = false;

  constructor(private commentsService: CommentsService) {}

  onReply(): void {
    this.showReplyForm = !this.showReplyForm;
    this.showEditForm = false;
  }

  onEdit(): void {
    this.showEditForm = !this.showEditForm;
    this.showReplyForm = false;
  }

  onDelete(): void {
    if (confirm('Are you sure you want to delete this comment?')) {
      this.commentsService.deleteComment(this.comment.id).subscribe(() => {
        this.commentChanged.emit();
      });
    }
  }

  onCommentSaved(comment: CommentDto): void {
    this.showReplyForm = false;
    this.showEditForm = false;
    this.commentChanged.emit();
  }
}

import {CommonModule} from '@angular/common';
import {Component, EventEmitter, Input, Output} from '@angular/core';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {MatCardModule} from '@angular/material/card';
import {MatIconModule} from '@angular/material/icon';
import {CommentDto} from '../../models/comment.dto';
import {CommentsService} from '../../services/comments.service';
import {CommentFormComponent} from '../comment-form/comment-form.component';
import {CommentAuthorComponent} from '../comment-author/comment-author.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';

@Component({
  selector: 'app-comment',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    MatButtonModule,
    MatCardModule,
    MatIconModule,
    CommentFormComponent,
    CommentAuthorComponent,
    ZxButtonComponent
  ],
  templateUrl: './comment.component.html',
  styleUrls: ['./comment.component.scss']
})
export class CommentComponent {
  @Input() comment!: CommentDto;
  @Output() commentChanged = new EventEmitter<void>();

  showReplyForm = false;
  showEditForm = false;

  constructor(
    private commentsService: CommentsService,
    private translate: TranslateService
  ) {}

  onReply(): void {
    this.showReplyForm = !this.showReplyForm;
    this.showEditForm = false;
  }

  onEdit(): void {
    this.showEditForm = !this.showEditForm;
    this.showReplyForm = false;
  }

  onDelete(): void {
    this.translate.get('comments.confirm-delete').subscribe((msg: string) => {
      if (confirm(msg)) {
        this.commentsService.deleteComment(this.comment.id).subscribe(() => {
          this.commentChanged.emit();
        });
      }
    });
  }

  onCommentSaved(comment: CommentDto): void {
    this.showReplyForm = false;
    this.showEditForm = false;
    this.commentChanged.emit();
  }
}

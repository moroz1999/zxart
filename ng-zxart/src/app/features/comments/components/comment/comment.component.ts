import {CommonModule} from '@angular/common';
import {Component, EventEmitter, Input, Output} from '@angular/core';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {MatIconModule} from '@angular/material/icon';
import {CommentDto} from '../../models/comment.dto';
import {CommentChangeEvent} from '../../models/comment-change-event';
import {CommentsService} from '../../services/comments.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {CommentFormComponent} from '../comment-form/comment-form.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxStackComponent} from "../../../../shared/ui/zx-stack/zx-stack.component";
import {
  ZxBodyDirective,
  ZxCaptionDirective,
  ZxLinkDirective
} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'zx-comment',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    MatIconModule,
    CommentFormComponent,
    ZxUserComponent,
    ZxButtonComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxBodyDirective,
    ZxCaptionDirective,
    ZxLinkDirective
  ],
  templateUrl: './comment.component.html',
  styleUrls: ['./comment.component.scss']
})
export class CommentComponent {
  @Input() comment!: CommentDto;
  @Input() showTarget = true;
  @Output() commentChanged = new EventEmitter<CommentChangeEvent>();

  showReplyForm = false;
  showEditForm = false;

  constructor(
    private commentsService: CommentsService,
    private translate: TranslateService,
    private sanitizer: DomSanitizer
  ) {}

  get hasImage(): boolean {
    return !!this.comment.target?.imageUrl;
  }

  get safeContent(): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(this.comment.content);
  }

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
          this.commentChanged.emit({type: 'delete', comment: this.comment});
        });
      }
    });
  }

  onCommentSaved(comment: CommentDto): void {
    this.showReplyForm = false;
    this.showEditForm = false;

    if (comment.id === this.comment.id) {
      this.comment.content = comment.content;
      this.comment.originalContent = comment.originalContent;
      this.comment.date = comment.date;
      this.comment.canEdit = comment.canEdit;
      this.comment.canDelete = comment.canDelete;
      this.commentChanged.emit({type: 'edit', comment});
      return;
    }

    if (comment.parentId === this.comment.id) {
      const children = Array.isArray(this.comment.children) ? this.comment.children : [];
      this.comment.children = [...children, comment];
      this.commentChanged.emit({type: 'reply', comment});
      return;
    }

    this.commentChanged.emit({type: 'edit', comment});
  }

  onChildChanged(event: CommentChangeEvent): void {
    if (event.type === 'delete') {
      const children = Array.isArray(this.comment.children) ? this.comment.children : [];
      this.comment.children = children.filter(child => child.id !== event.comment.id);
    }
    this.commentChanged.emit(event);
  }
}

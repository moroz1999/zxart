import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input, signal} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {CommentDto} from '../../models/comment.dto';
import {CommentChangeEvent} from '../../models/comment-change-event';
import {CommentsService} from '../../services/comments.service';
import {CommentComponent} from '../comment/comment.component';
import {CommentFormComponent} from '../comment-form/comment-form.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ViewportLoaderComponent} from '../../../../shared/components/viewport-loader/viewport-loader.component';
import {Observable, of, Subject} from 'rxjs';
import {ZxBodyDirective} from "../../../../shared/directives/typography/typography.directives";

@Component({
  selector: 'zx-comments-list,zx-comments-list-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    CommentComponent,
    CommentFormComponent,
    ZxButtonComponent,
    ZxStackComponent,
    ZxPanelComponent,
    ViewportLoaderComponent,
    ZxBodyDirective
  ],
  templateUrl: './comments-list.component.html',
  styleUrls: ['./comments-list.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentsListComponent {
  @Input() elementId?: number;
  @Input() comments: CommentDto[] = [];
  @Input() isRoot: boolean = true;

  get showTarget(): boolean {
    return !this.elementId;
  }

  readonly showForm = signal(false);

  reloadSubject = new Subject<void>();

  constructor(private commentsService: CommentsService) {}

  getCommentsLoader = (): Observable<CommentDto[]> => {
    if (this.isRoot && this.elementId) {
      return this.commentsService.getComments(this.elementId);
    }
    return of(this.comments);
  };

  onCommentSaved(comment: CommentDto): void {
    this.showForm.set(false);
    if (this.isRoot) {
      this.reloadSubject.next();
    }
  }

  toggleForm(): void {
    this.showForm.update(showForm => !showForm);
  }

  hideForm(): void {
    this.showForm.set(false);
  }

  onCommentChanged(event: CommentChangeEvent): void {
    if (!this.isRoot) {
      return;
    }
    if (event.type === 'delete' && (!event.comment.parentId || event.comment.parentId === 0)) {
      this.reloadSubject.next();
    }
  }
}

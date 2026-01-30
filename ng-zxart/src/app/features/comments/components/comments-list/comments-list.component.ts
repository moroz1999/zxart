import {CommonModule} from '@angular/common';
import {Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {MatButtonModule} from '@angular/material/button';
import {MatDividerModule} from '@angular/material/divider';
import {CommentDto} from '../../models/comment.dto';
import {CommentsService} from '../../services/comments.service';
import {CommentComponent} from '../comment/comment.component';
import {CommentFormComponent} from '../comment-form/comment-form.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxHeading3Directive} from '../../../../shared/directives/typography/typography.directives';
import {ViewportLoaderComponent} from '../../../../shared/components/viewport-loader/viewport-loader.component';
import {Observable, of, Subject} from 'rxjs';

@Component({
  selector: 'app-comments-list',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    MatButtonModule,
    MatDividerModule,
    CommentComponent,
    CommentFormComponent,
    ZxButtonComponent,
    ZxStackComponent,
    ZxPanelComponent,
    ZxHeading3Directive,
    ViewportLoaderComponent
  ],
  templateUrl: './comments-list.component.html',
  styleUrls: ['./comments-list.component.scss']
})
export class CommentsListComponent {
  @Input() elementId?: number;
  @Input() comments: CommentDto[] = [];
  @Input() isRoot: boolean = true;

  showForm = false;

  reloadSubject = new Subject<void>();

  constructor(private commentsService: CommentsService) {}

  getCommentsLoader = (): Observable<CommentDto[]> => {
    if (this.isRoot && this.elementId) {
      return this.commentsService.getComments(this.elementId);
    }
    return of(this.comments);
  };

  onCommentSaved(comment: CommentDto): void {
    this.showForm = false;
    if (this.isRoot) {
      this.reloadSubject.next();
    }
  }
}

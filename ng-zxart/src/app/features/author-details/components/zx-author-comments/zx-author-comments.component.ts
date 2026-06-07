import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {CommentsService} from '../../../comments/services/comments.service';
import {CommentsListDto} from '../../../comments/models/comment.dto';
import {ZxWorksCommentsPanelComponent} from '../../../../entities/zx-works-comments-panel/zx-works-comments-panel.component';

const COMMENTS_PAGE_SIZE = 10;

@Component({
  selector: 'zx-author-comments',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxWorksCommentsPanelComponent],
  template: `
    <zx-works-comments-panel
      [title]="'author.comments-on-works' | translate"
      [loader]="loader">
    </zx-works-comments-panel>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorCommentsComponent {
  @Input() elementId = 0;

  constructor(private readonly commentsService: CommentsService) {}

  loader = (page: number): Observable<CommentsListDto> =>
    this.commentsService.getAuthorComments(this.elementId, page, COMMENTS_PAGE_SIZE);
}

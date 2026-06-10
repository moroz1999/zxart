import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {CommentsService} from '../../../comments/services/comments.service';
import {CommentsListDto} from '../../../comments/models/comment.dto';
import {WorksCommentsComponent} from '../../../comments/components/works-comments/works-comments.component';

const COMMENTS_PAGE_SIZE = 10;

@Component({
  selector: 'zx-party-comments',
  standalone: true,
  imports: [CommonModule, TranslateModule, WorksCommentsComponent],
  template: `
    <zx-works-comments
      [title]="'party-details.activity.comments' | translate"
      [loader]="loader">
    </zx-works-comments>
  `,
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyCommentsComponent {
  @Input() elementId = 0;

  constructor(private readonly commentsService: CommentsService) {}

  loader = (page: number): Observable<CommentsListDto> =>
    this.commentsService.getPartyComments(this.elementId, page, COMMENTS_PAGE_SIZE);
}

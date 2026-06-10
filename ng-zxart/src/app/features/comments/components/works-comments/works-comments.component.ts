import {ChangeDetectionStrategy, Component, Input, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {CommentDto, CommentsListDto} from '../../models/comment.dto';
import {CommentChangeEvent} from '../../models/comment-change-event';
import {CommentComponent} from '../comment/comment.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {
  ZxCommentSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-comment-skeleton/zx-comment-skeleton.component';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';

/**
 * "Comments on works" list: paginated, lazy-loaded, rendered with the canonical rich
 * {@link CommentComponent} (work thumbnail via `showTarget`, reply/edit/delete, nested replies).
 * The single component used by the author, group and party activity tabs — the host supplies a
 * title and a page loader so it stays element-agnostic.
 */
@Component({
  selector: 'zx-works-comments',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    CommentComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxPaginationComponent,
    ZxCommentSkeletonComponent,
    InViewportDirective,
  ],
  templateUrl: './works-comments.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class WorksCommentsComponent {
  @Input() title = '';
  @Input() loader!: (page: number) => Observable<CommentsListDto>;

  comments = signal<CommentDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);
  currentPage = signal(1);
  pagesAmount = signal(0);
  totalCount = signal(0);

  onInViewport(): void {
    if (!this.hasLoaded() && !this.loading()) {
      this.loadPage(1);
    }
  }

  onPageChange(page: number): void {
    this.loadPage(page);
  }

  onCommentChanged(_: CommentChangeEvent): void {
    this.loadPage(this.currentPage());
  }

  private loadPage(page: number): void {
    if (!this.loader) {
      return;
    }
    this.loading.set(true);
    this.loader(page).subscribe(response => {
      this.comments.set(response.comments);
      this.currentPage.set(response.currentPage);
      this.pagesAmount.set(response.pagesAmount);
      this.totalCount.set(response.totalCount);
      this.loading.set(false);
      this.hasLoaded.set(true);
    });
  }
}

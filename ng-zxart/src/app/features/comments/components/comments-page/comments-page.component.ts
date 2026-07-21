import {ChangeDetectionStrategy, Component, inject, Input, OnDestroy, OnInit, signal} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {Subscription} from 'rxjs';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CommentsService} from '../../services/comments.service';
import {CommentsListDto} from '../../models/comment.dto';
import {CommentChangeEvent} from '../../models/comment-change-event';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {
  ZxCommentSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-comment-skeleton/zx-comment-skeleton.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {CommentComponent} from '../comment/comment.component';
import {ZxLoadingStateDirective} from '../../../../shared/ui/zx-loading-state/zx-loading-state.directive';

@Component({
  selector: 'zx-comments-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPaginationComponent,
    ZxStackComponent,
    ZxCommentSkeletonComponent,
    HeadingDirective,
    CommentComponent,
    ZxLoadingStateDirective,
  ],
  templateUrl: './comments-page.component.html',
  styleUrls: ['./comments-page.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class CommentsPageComponent implements OnInit, OnDestroy {
  @Input() title = '';
  @Input() urlBase = '';
  /** Legacy embeds sync the page to `window.location`; the SPA route uses a router query param. */
  @Input() manageUrl = true;

  data = signal<CommentsListDto | null>(null);
  initialLoading = signal(true);
  paginationLoading = signal(false);
  currentPage = signal(1);

  private readonly subscriptions = new Subscription();
  private readonly router = inject(Router, {optional: true});
  private readonly route = inject(ActivatedRoute, {optional: true});

  constructor(private commentsService: CommentsService) {}

  ngOnInit(): void {
    if (this.useRouter) {
      let first = true;
      this.subscriptions.add(this.route!.queryParams.subscribe(params => {
        const page = params['page'] ? +params['page'] : 1;
        this.loadComments(page, first);
        first = false;
      }));
    } else {
      this.loadComments(this.parsePageFromUrl(), true);
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  /** SPA route mode: the page number lives in / comes from the router query params. */
  private get useRouter(): boolean {
    return !this.manageUrl && this.router != null && this.route != null;
  }

  loadComments(page: number, isInitial = false): void {
    if (isInitial) {
      this.initialLoading.set(true);
    } else {
      this.paginationLoading.set(true);
    }
    this.currentPage.set(page);

    this.commentsService.getAllComments(page).subscribe({
      next: (data) => {
        this.data.set(data);
        this.initialLoading.set(false);
        this.paginationLoading.set(false);
      },
      error: () => {
        this.initialLoading.set(false);
        this.paginationLoading.set(false);
      }
    });
  }

  onPageChange(page: number): void {
    if (this.useRouter) {
      this.router!.navigate([], {
        relativeTo: this.route!,
        queryParams: {page: page > 1 ? page : null},
      });
    } else {
      this.loadComments(page, false);
      this.updateUrl(page);
    }
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  onCommentChanged(event: CommentChangeEvent): void {
    if (event.type === 'delete' || event.type === 'reply') {
      this.loadComments(this.currentPage(), false);
    }
  }

  private parsePageFromUrl(): number {
    const path = window.location.pathname;
    const match = path.match(/\/page:(\d+)/);
    if (match) {
      const page = parseInt(match[1], 10);
      return page > 0 ? page : 1;
    }
    return 1;
  }

  private updateUrl(page: number): void {
    const currentPath = window.location.pathname;
    const cleanPath = currentPath.replace(/\/page:\d+\/?/, '');
    const basePath = cleanPath.endsWith('/') ? cleanPath : cleanPath + '/';
    const newPath = page > 1 ? basePath + 'page:' + page + '/' : basePath;
    window.history.pushState(null, '', newPath);
  }
}

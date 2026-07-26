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

  data = signal<CommentsListDto | null>(null);
  initialLoading = signal(true);
  paginationLoading = signal(false);
  currentPage = signal(1);

  private readonly subscriptions = new Subscription();
  private readonly router = inject(Router);
  private readonly route = inject(ActivatedRoute);

  constructor(private commentsService: CommentsService) {}

  ngOnInit(): void {
    let first = true;
    this.subscriptions.add(this.route.queryParams.subscribe(params => {
      const page = params['page'] ? +params['page'] : 1;
      this.loadComments(page, first);
      first = false;
    }));
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
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
    this.router.navigate([], {
      relativeTo: this.route,
      queryParams: {page: page > 1 ? page : null},
    });
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  onCommentChanged(event: CommentChangeEvent): void {
    if (event.type === 'delete' || event.type === 'reply') {
      this.loadComments(this.currentPage(), false);
    }
  }

}

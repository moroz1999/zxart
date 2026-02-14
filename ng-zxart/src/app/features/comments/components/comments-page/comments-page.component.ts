import {Component, Inject, Input, OnInit, PLATFORM_ID, signal} from '@angular/core';
import {CommonModule, isPlatformBrowser} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CommentsService} from '../../services/comments.service';
import {CommentsListDto} from '../../models/comment.dto';
import {CommentChangeEvent} from '../../models/comment-change-event';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {CommentComponent} from '../comment/comment.component';

@Component({
  selector: 'zx-comments-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPaginationComponent,
    ZxStackComponent,
    ZxSkeletonComponent,
    ZxHeading2Directive,
    CommentComponent
  ],
  templateUrl: './comments-page.component.html',
  styleUrls: ['./comments-page.component.scss']
})
export class CommentsPageComponent implements OnInit {
  @Input() title = '';
  @Input() urlBase = '';

  data = signal<CommentsListDto | null>(null);
  initialLoading = signal(true);
  paginationLoading = signal(false);
  currentPage = signal(1);

  private readonly isBrowser: boolean;

  constructor(
    private commentsService: CommentsService,
    @Inject(PLATFORM_ID) platformId: object
  ) {
    this.isBrowser = isPlatformBrowser(platformId);
  }

  ngOnInit(): void {
    const page = this.parsePageFromUrl();
    this.loadComments(page, true);
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
    this.loadComments(page, false);
    this.updateUrl(page);
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  onCommentChanged(event: CommentChangeEvent): void {
    if (event.type === 'delete' || event.type === 'reply') {
      this.loadComments(this.currentPage(), false);
    }
  }

  private parsePageFromUrl(): number {
    if (!this.isBrowser) {
      return 1;
    }
    const path = window.location.pathname;
    const match = path.match(/\/page:(\d+)/);
    if (match) {
      const page = parseInt(match[1], 10);
      return page > 0 ? page : 1;
    }
    return 1;
  }

  private updateUrl(page: number): void {
    if (!this.isBrowser) {
      return;
    }
    const currentPath = window.location.pathname;
    const cleanPath = currentPath.replace(/\/page:\d+\/?/, '');
    const basePath = cleanPath.endsWith('/') ? cleanPath : cleanPath + '/';
    const newPath = page > 1 ? basePath + 'page:' + page + '/' : basePath;
    window.history.pushState(null, '', newPath);
  }
}

import {ChangeDetectionStrategy, Component, Input, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';
import {Observable} from 'rxjs';
import {CommentDto, CommentsListDto} from '../../features/comments/models/comment.dto';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxUserComponent} from '../zx-user/zx-user.component';
import {ZxPaginationComponent} from '../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxTextSkeletonComponent} from '../../shared/ui/zx-skeleton/components/zx-text-skeleton/zx-text-skeleton.component';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {InViewportDirective} from '../../shared/directives/in-viewport.directive';

/**
 * Shared "comments on works" panel: a paginated, lazy-loaded list of comments rendered with
 * zx-stack + zx-user. Reused by the author and group discussion tabs; the host supplies the
 * title and a page loader so the panel stays element-agnostic.
 */
@Component({
  selector: 'zx-works-comments-panel',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxStackComponent,
    ZxUserComponent,
    ZxPaginationComponent,
    ZxTextSkeletonComponent,
    TextDirective,
    InViewportDirective,
  ],
  templateUrl: './zx-works-comments-panel.component.html',
  styleUrl: './zx-works-comments-panel.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxWorksCommentsPanelComponent {
  @Input() title = '';
  @Input() loader!: (page: number) => Observable<CommentsListDto>;

  comments = signal<CommentDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);
  currentPage = signal(1);
  pagesAmount = signal(0);
  totalCount = signal(0);

  constructor(private readonly sanitizer: DomSanitizer) {}

  onInViewport(): void {
    if (!this.hasLoaded() && !this.loading()) {
      this.loadPage(1);
    }
  }

  onPageChange(page: number): void {
    this.loadPage(page);
  }

  sanitizeHtml(content: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(content);
  }

  displayContent(comment: CommentDto): string {
    return comment.translated?.trim() ? comment.translated : comment.content;
  }

  getCommentUrl(comment: CommentDto): string {
    if (!comment.target) {
      return '';
    }
    const url = new URL(comment.target.url, window.location.origin);
    if (comment.target.type === 'zxProd') {
      const cleanPath = url.pathname.replace(/\/tabs:[^/]+(?=\/|$)/, '');
      const normalized = cleanPath.endsWith('/') ? cleanPath : `${cleanPath}/`;
      url.pathname = `${normalized}tabs:${encodeURIComponent('discussion')}/`;
    }
    url.hash = `comment${comment.id}`;
    return `${url.pathname}${url.search}${url.hash}`;
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

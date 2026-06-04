import {ChangeDetectionStrategy, Component, Input, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';
import {CommentsService} from '../../../comments/services/comments.service';
import {CommentDto} from '../../../comments/models/comment.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxUserComponent} from '../../../../entities/zx-user/zx-user.component';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxTextSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-text-skeleton/zx-text-skeleton.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';

const COMMENTS_PAGE_SIZE = 10;

@Component({
  selector: 'zx-author-comments',
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
  templateUrl: './zx-author-comments.component.html',
  styleUrl: './zx-author-comments.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorCommentsComponent {
  @Input() elementId = 0;

  comments = signal<CommentDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);
  currentPage = signal(1);
  pagesAmount = signal(0);
  totalCount = signal(0);

  constructor(
    private commentsService: CommentsService,
    private sanitizer: DomSanitizer,
  ) {}

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
    if (!this.elementId) {
      return;
    }
    this.loading.set(true);
    this.commentsService.getAuthorComments(this.elementId, page, COMMENTS_PAGE_SIZE).subscribe(response => {
      this.comments.set(response.comments);
      this.currentPage.set(response.currentPage);
      this.pagesAmount.set(response.pagesAmount);
      this.totalCount.set(response.totalCount);
      this.loading.set(false);
      this.hasLoaded.set(true);
    });
  }
}

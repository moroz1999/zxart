import {ChangeDetectionStrategy, Component, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {toSignal} from '@angular/core/rxjs-interop';
import {map} from 'rxjs/operators';
import {CommentsService} from '../../services/comments.service';
import {CommentDto} from '../../models/comment.dto';
import {BackendLinksService} from '../../../header/services/backend-links.service';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {
  ZxTextSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-text-skeleton/zx-text-skeleton.component';
import {
  ZxSkeletonBoneComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxUserComponent} from '../../../../entities/zx-user/zx-user.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';

@Component({
  selector: 'zx-latest-comments',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxStackComponent,
    ZxTextSkeletonComponent,
    ZxSkeletonBoneComponent,
    ZxUserComponent,
    ZxPanelComponent,
    TextDirective,
    ZxButtonComponent,
  ],
  templateUrl: './latest-comments.component.html',
  styleUrls: ['./latest-comments.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LatestCommentsComponent implements OnInit {
  readonly allCommentsUrl = toSignal(
    this.backendLinksService.links$.pipe(map(l => l.commentsUrl ?? '')),
    {initialValue: ''},
  );

  comments = signal<CommentDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);

  constructor(
    private commentsService: CommentsService,
    private backendLinksService: BackendLinksService,
    private sanitizer: DomSanitizer,
  ) {}

  ngOnInit(): void {
    this.loadComments();
  }

  private loadComments(): void {
    this.loading.set(true);
    this.commentsService.getLatestComments(10).subscribe({
      next: (comments) => {
        this.comments.set(comments);
        this.loading.set(false);
        this.hasLoaded.set(true);
      },
      error: () => {
        this.loading.set(false);
        this.hasLoaded.set(true);
      },
    });
  }

  sanitizeHtml(content: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(content);
  }

  displayContent(comment: CommentDto): string {
    return comment.translated.trim() !== '' ? comment.translated : comment.content;
  }

  getCommentUrl(comment: CommentDto): string {
    if (!comment.target) {
      return '';
    }

    const url = new URL(comment.target.url, window.location.origin);
    if (comment.target.type === 'zxProd') {
      url.pathname = this.replaceTabPath(url.pathname, 'discussion');
    }
    url.hash = `comment${comment.id}`;

    return `${url.pathname}${url.search}${url.hash}`;
  }

  private replaceTabPath(path: string, tabId: string): string {
    const cleanPath = path.replace(/\/tabs:[^/]+(?=\/|$)/, '');
    const normalizedPath = cleanPath.endsWith('/') ? cleanPath : `${cleanPath}/`;

    return `${normalizedPath}tabs:${encodeURIComponent(tabId)}/`;
  }
}

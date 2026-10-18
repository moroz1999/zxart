import {ChangeDetectionStrategy, Component, computed, DestroyRef, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';
import {takeUntilDestroyed} from '@angular/core/rxjs-interop';
import {TranslateModule} from '@ngx-translate/core';
import {CommentsService} from '../../services/comments.service';
import {CommentDto} from '../../models/comment.dto';
import {CommentLink, commentLink} from '../../utils/comment-link';
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
    RouterLink,
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
  readonly allCommentsUrl = signal('/comments');

  comments = signal<CommentDto[]>([]);
  loading = signal(false);
  hasLoaded = signal(false);
  /** Link to each comment that has a target, by comment id. */
  readonly links = computed(() => {
    const links = new Map<number, CommentLink>();
    for (const comment of this.comments()) {
      if (comment.target) {
        links.set(comment.id, commentLink(comment.id, comment.target));
      }
    }
    return links;
  });

  constructor(
    private commentsService: CommentsService,
    private sanitizer: DomSanitizer,
    private destroyRef: DestroyRef,
  ) {}

  ngOnInit(): void {
    this.loadComments();
  }

  private loadComments(): void {
    this.loading.set(true);
    this.commentsService.getLatestComments(10).pipe(
      takeUntilDestroyed(this.destroyRef),
    ).subscribe({
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
}

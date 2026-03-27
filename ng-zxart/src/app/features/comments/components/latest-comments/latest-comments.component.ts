import {ChangeDetectionStrategy, Component, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {toSignal} from '@angular/core/rxjs-interop';
import {map} from 'rxjs/operators';
import {CommentsService} from '../../services/comments.service';
import {CommentDto} from '../../models/comment.dto';
import {BackendLinksService} from '../../../header/services/backend-links.service';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxBodySmMutedDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';

@Component({
  selector: 'zx-latest-comments',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxStackComponent,
    ZxSkeletonComponent,
    ZxUserComponent,
    ZxPanelComponent,
    ZxBodySmMutedDirective,
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
}

import {Component, Input, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CommentsService} from '../../services/comments.service';
import {CommentDto} from '../../models/comment.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxCaptionDirective, ZxHeading3Directive,} from '../../../../shared/directives/typography/typography.directives';
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
    ZxCaptionDirective,
    ZxHeading3Directive,
    ZxButtonComponent
  ],
  templateUrl: './latest-comments.component.html',
  styleUrls: ['./latest-comments.component.scss']
})
export class LatestCommentsComponent implements OnInit {
  @Input() allCommentsUrl = '';

  comments = signal<CommentDto[]>([]);
  loading = signal(true);

  constructor(
    private commentsService: CommentsService,
    private sanitizer: DomSanitizer
  ) {
  }

  ngOnInit(): void {
    this.commentsService.getLatestComments(10).subscribe({
      next: (comments) => {
        this.comments.set(comments);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
      }
    });
  }

  sanitizeHtml(content: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(content);
  }
}

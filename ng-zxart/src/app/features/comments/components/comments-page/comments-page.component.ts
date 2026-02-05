import {Component, Input, OnInit, signal} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {CommentsService} from '../../services/comments.service';
import {CommentDto, CommentsListDto} from '../../models/comment.dto';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxUserComponent} from '../../../../shared/ui/zx-user/zx-user.component';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {
  ZxBodyDirective,
  ZxCaptionDirective,
  ZxHeading2Directive,
  ZxLinkDirective
} from '../../../../shared/directives/typography/typography.directives';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';

@Component({
  selector: 'zx-comments-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPaginationComponent,
    ZxStackComponent,
    ZxPanelComponent,
    ZxUserComponent,
    ZxSkeletonComponent,
    ZxBodyDirective,
    ZxCaptionDirective,
    ZxHeading2Directive,
    ZxLinkDirective
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

  constructor(
    private commentsService: CommentsService,
    private sanitizer: DomSanitizer
  ) {}

  ngOnInit(): void {
    this.loadComments(1, true);
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
    window.scrollTo({top: 0, behavior: 'smooth'});
  }

  sanitizeHtml(content: string): SafeHtml {
    return this.sanitizer.bypassSecurityTrustHtml(content);
  }

  hasImage(comment: CommentDto): boolean {
    return !!comment.target?.imageUrl;
  }

  getTargetTypeClass(comment: CommentDto): string {
    return comment.target?.type || '';
  }
}

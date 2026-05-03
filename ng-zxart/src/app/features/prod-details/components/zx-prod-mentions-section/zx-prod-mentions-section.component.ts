import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxCommentSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-comment-skeleton/zx-comment-skeleton.component';
import {ZxArticlePreviewComponent} from '../../../../shared/ui/zx-article-preview/zx-article-preview.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ProdMentionsApiService} from '../../services/prod-mentions-api.service';
import {PressArticlePreviewDto} from '../../models/press-article.dto';

@Component({
  selector: 'zx-prod-mentions-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxCommentSkeletonComponent,
    ZxArticlePreviewComponent,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-prod-mentions-section.component.html',
  styleUrls: ['./zx-prod-mentions-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdMentionsSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  mentions: PressArticlePreviewDto[] = [];

  constructor(
    private readonly api: ProdMentionsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.api.getMentions(this.elementId).subscribe(mentions => {
      this.mentions = mentions;
      this.loaded = true;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  trackById(_index: number, article: PressArticlePreviewDto): number {
    return article.id;
  }
}

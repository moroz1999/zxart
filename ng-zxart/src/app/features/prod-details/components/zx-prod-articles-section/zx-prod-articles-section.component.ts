import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxCommentSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-comment-skeleton/zx-comment-skeleton.component';
import {ZxArticlePreviewComponent} from '../../../../shared/ui/zx-article-preview/zx-article-preview.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ProdArticlesApiService} from '../../services/prod-articles-api.service';
import {PressArticlePreviewDto} from '../../models/press-article.dto';

@Component({
  selector: 'zx-prod-articles-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxCommentSkeletonComponent,
    ZxArticlePreviewComponent,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-prod-articles-section.component.html',
  styleUrls: ['./zx-prod-articles-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdArticlesSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  articles: PressArticlePreviewDto[] = [];

  constructor(
    private readonly api: ProdArticlesApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.loading = true;
    this.api.getArticles(this.elementId).subscribe(articles => {
      this.articles = articles;
      this.loaded = true;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  trackById(_index: number, article: PressArticlePreviewDto): number {
    return article.id;
  }
}

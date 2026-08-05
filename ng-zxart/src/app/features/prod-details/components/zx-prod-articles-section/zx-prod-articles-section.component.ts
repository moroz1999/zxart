import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxArticlePreviewComponent} from '../../../../entities/zx-article-preview/zx-article-preview.component';
import {
  ZxArticlePreviewSkeletonComponent
} from '../../../../entities/zx-article-preview-skeleton/zx-article-preview-skeleton.component';
import {ProdArticlesApiService} from '../../services/prod-articles-api.service';
import {PressArticlePreviewDto} from '../../models/press-article.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

/** The production's own press articles; the articles tab names the section. */
@Component({
  selector: 'zx-prod-articles-section',
  standalone: true,
  imports: [
    CommonModule,
    InViewportDirective,
    ZxArticlePreviewComponent,
    ZxArticlePreviewSkeletonComponent,
    ZxStackComponent,
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

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.articles.length === 0 ? 'none' : '';
  }

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

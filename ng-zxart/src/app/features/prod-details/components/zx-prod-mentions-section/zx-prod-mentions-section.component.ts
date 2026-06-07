import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxCommentSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-comment-skeleton/zx-comment-skeleton.component';
import {ZxPressMentionsListComponent} from '../../../../entities/zx-press-mentions-list/zx-press-mentions-list.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ProdMentionsApiService} from '../../services/prod-mentions-api.service';
import {PressArticlePreviewDto} from '../../models/press-article.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-prod-mentions-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxCommentSkeletonComponent,
    ZxPressMentionsListComponent,
    HeadingDirective,
    ZxStackComponent,
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

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.mentions.length === 0 ? 'none' : '';
  }

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
}

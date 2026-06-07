import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {AuthorMentionsApiService} from '../../services/author-mentions-api.service';
import {PressArticlePreviewDto} from '../../../prod-details/models/press-article.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxRowSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-row-skeleton/zx-row-skeleton.component';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ZxPressMentionsListComponent} from '../../../../entities/zx-press-mentions-list/zx-press-mentions-list.component';

@Component({
  selector: 'zx-author-mentions',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxStackComponent,
    ZxRowSkeletonComponent,
    InViewportDirective,
    HeadingDirective,
    ZxPressMentionsListComponent,
  ],
  templateUrl: './zx-author-mentions.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorMentionsComponent implements OnDestroy {
  @Input() elementId = 0;

  mentions: PressArticlePreviewDto[] = [];
  loading = false;
  loaded = false;
  requested = false;

  private readonly subscriptions = new Subscription();

  constructor(
    private readonly api: AuthorMentionsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.requested) {
      return;
    }
    this.requested = true;
    this.loading = true;
    this.subscriptions.add(
      this.api.getMentions(this.elementId).subscribe(mentions => {
        this.mentions = mentions;
        this.loading = false;
        this.loaded = true;
        this.cdr.markForCheck();
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }
}

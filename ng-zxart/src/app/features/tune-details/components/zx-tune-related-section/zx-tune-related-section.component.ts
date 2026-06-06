import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {TuneRelatedRailKind} from '../../models/tune-details.dto';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {TuneDetailsApiService} from '../../services/tune-details-api.service';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxPartyPlaceComponent} from '../../../../shared/lib/zx-party-place/zx-party-place.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';

interface RelatedRail {
  readonly kind: TuneRelatedRailKind;
  readonly items: ZxTuneDto[];
}

@Component({
  selector: 'zx-tune-related-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxPanelComponent,
    ZxGridComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxPartyPlaceComponent,
    TextDirective,
    HeadingDirective,
  ],
  templateUrl: './zx-tune-related-section.component.html',
  styleUrls: ['./zx-tune-related-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTuneRelatedSectionComponent implements OnDestroy {
  @Input({required: true}) tuneId!: number;

  loaded = false;
  loading = false;

  // Fixed display order; only rails with items are shown.
  private readonly kinds: ReadonlyArray<TuneRelatedRailKind> = ['author', 'tags', 'tracker'];
  private readonly railsMap = new Map<TuneRelatedRailKind, ZxTuneDto[]>();
  private readonly titleKeys: Record<TuneRelatedRailKind, string> = {
    author: 'tune-details.related-author',
    tags: 'tune-details.related-tags',
    tracker: 'tune-details.related-tracker',
  };
  private readonly subscriptions: Subscription[] = [];

  constructor(
    private readonly api: TuneDetailsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.railsMap.size === 0 ? 'none' : '';
  }

  get rails(): RelatedRail[] {
    return this.kinds
      .filter(kind => (this.railsMap.get(kind)?.length ?? 0) > 0)
      .map(kind => ({kind, items: this.railsMap.get(kind)!}));
  }

  onInViewport(): void {
    if (this.loaded || this.loading || !this.tuneId) {
      return;
    }
    this.loading = true;
    let pending = this.kinds.length;
    // Each rail is fetched as its own request.
    for (const kind of this.kinds) {
      this.subscriptions.push(
        this.api.getRelated(this.tuneId, kind).subscribe(items => {
          if (items.length) {
            this.railsMap.set(kind, items);
          }
          if (--pending === 0) {
            this.loaded = true;
            this.loading = false;
          }
          this.cdr.markForCheck();
        }),
      );
    }
  }

  ngOnDestroy(): void {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }

  railTitleKey(kind: TuneRelatedRailKind): string {
    return this.titleKeys[kind];
  }

  authorNames(authors: {name: string}[]): string {
    return authors.map(author => author.name).join(', ');
  }
}

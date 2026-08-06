import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay, tap} from 'rxjs/operators';
import {TuneDetailsDto, TuneDownloadDto} from '../../models/tune-details.dto';
import {TuneDetailsApiService} from '../../services/tune-details-api.service';

import {BreadcrumbService} from '../../../../shared/services/breadcrumb.service';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxGridItemDirective} from '../../../../shared/ui/zx-grid/zx-grid-item.directive';
import {TagsListComponent} from '../../../../shared/lib/tags-list/tags-list.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxCalloutComponent} from '../../../../shared/ui/zx-callout/zx-callout.component';
import {ZxAddedByComponent} from '../../../../shared/ui/zx-added-by/zx-added-by.component';
import {ZxPartyProvenanceComponent} from '../../../../shared/lib/zx-party-provenance/zx-party-provenance.component';
import {ZxProdContextComponent} from '../../../../entities/zx-prod-context/components/zx-prod-context/zx-prod-context.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxPreformattedComponent} from '../../../../shared/ui/zx-preformatted/zx-preformatted.component';
import {ZxHeroComponent} from '../../../../shared/ui/zx-hero/zx-hero.component';
import {ZxHeroTitleComponent} from '../../../../shared/ui/zx-hero-title/zx-hero-title.component';
import {ZxHeroBarComponent} from '../../../../shared/ui/zx-hero-bar/zx-hero-bar.component';
import {ZxChipComponent} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxCounterItem, ZxCountersComponent} from '../../../../shared/ui/zx-counters/zx-counters.component';
import {ZxCreditGroup, ZxCreditsRowComponent} from '../../../../shared/ui/zx-credits-row/zx-credits-row.component';
import {ZxDownloadButtonComponent} from '../../../../shared/ui/zx-download-button/zx-download-button.component';

import {ZxTunePlayerComponent} from '../zx-tune-player/zx-tune-player.component';
import {ZxTuneMetaPanelComponent} from '../zx-tune-meta-panel/zx-tune-meta-panel.component';
import {ZxTuneDownloadsPanelComponent} from '../zx-tune-downloads-panel/zx-tune-downloads-panel.component';
import {ZxTuneRelatedSectionComponent} from '../zx-tune-related-section/zx-tune-related-section.component';
import {ZxTuneEditingControlsComponent} from '../zx-tune-editing-controls/zx-tune-editing-controls.component';
import {ZxTuneDetailsSkeletonComponent} from '../zx-tune-details-skeleton/zx-tune-details-skeleton.component';

import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';
import {PageMetadataService} from '../../../../shared/services/page-metadata.service';


@Component({
  selector: 'zx-tune-details-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxGridComponent,
    ZxGridItemDirective,
    TagsListComponent,
    ZxItemControlsComponent,
    ZxCalloutComponent,
    ZxAddedByComponent,
    ZxPartyProvenanceComponent,
    ZxProdContextComponent,
    TextDirective,
    ZxPreformattedComponent,
    ZxHeroComponent,
    ZxHeroTitleComponent,
    ZxHeroBarComponent,
    ZxChipComponent,
    ZxButtonControlsComponent,
    ZxCountersComponent,
    ZxCreditsRowComponent,
    ZxDownloadButtonComponent,
    ZxTunePlayerComponent,
    ZxTuneMetaPanelComponent,
    ZxTuneDownloadsPanelComponent,
    ZxTuneRelatedSectionComponent,
    ZxTuneEditingControlsComponent,
    ZxTuneDetailsSkeletonComponent,
    CommentsListComponent,
    RatingsListComponent,
  ],
  templateUrl: './zx-tune-details.component.html',
  styleUrls: ['./zx-tune-details.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTuneDetailsComponent implements OnChanges {
  @Input() elementId = 0;
  @Output() pageTitleChange = new EventEmitter<string>();

  details$: Observable<TuneDetailsDto | null> = of(null);

  constructor(
    private readonly api: TuneDetailsApiService,
    private readonly breadcrumbService: BreadcrumbService,
    private readonly translate: TranslateService,
    private readonly pageMetadataService: PageMetadataService,
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (!changes['elementId']) {
      return;
    }
    if (!this.elementId || +this.elementId <= 0) {
      this.details$ = of(null);
      return;
    }
    this.details$ = this.api.getDetails(+this.elementId).pipe(
      tap(details => {
        if (details) {
          this.pageTitleChange.emit(details.title);
          this.pageMetadataService.applyEntityMetadata(details.metadata);
          this.breadcrumbService.setEntityTrail({
            items: [
              {title: this.translate.instant('menu.music'), url: '/music'},
              ...(details.authors.length ? [{title: details.authors[0].name, url: details.authors[0].url}] : []),
            ],
            currentTitle: details.title,
          });
        } else {
          this.breadcrumbService.setNotFoundTrail();
        }
      }),
      shareReplay(1),
    );
  }

  formatSearchUrl(details: TuneDetailsDto): string {
    return `/music/search?format=${encodeURIComponent(details.format)}`;
  }

  creditGroups(details: TuneDetailsDto): ZxCreditGroup[] {
    return [
      {
        labelKey: 'tune-details.musician',
        people: details.authors.map(author => ({title: author.name, url: author.url})),
      },
    ];
  }

  counters(details: TuneDetailsDto): ZxCounterItem[] {
    const items: ZxCounterItem[] = [];
    if (details.votes > 0) {
      items.push({value: details.votes.toFixed(2), labelKey: 'hero.rating'});
    }
    items.push({value: details.votesAmount, labelKey: 'hero.votes'});
    items.push({value: details.plays, labelKey: 'tune-details.plays'});
    return items;
  }

  /** The main file offered in the hero bar; the full list stays in the downloads panel. */
  primaryDownload(details: TuneDetailsDto): TuneDownloadDto | null {
    return details.downloads[0] ?? null;
  }
}

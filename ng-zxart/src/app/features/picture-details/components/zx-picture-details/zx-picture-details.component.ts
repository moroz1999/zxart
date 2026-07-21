import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, OnDestroy, Output, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable, of, Subscription} from 'rxjs';
import {filter, shareReplay, take, tap} from 'rxjs/operators';
import {PictureDetailsDto} from '../../models/picture-details.dto';
import {PictureDetailsApiService} from '../../services/picture-details-api.service';
import {AnalyticsService} from '../../../../shared/services/analytics.service';
import {PageMetadataService} from '../../../../shared/services/page-metadata.service';

import {BreadcrumbService} from '../../../../shared/services/breadcrumb.service';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxGridItemDirective} from '../../../../shared/ui/zx-grid/zx-grid-item.directive';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {TagsListComponent} from '../../../../shared/lib/tags-list/tags-list.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxRatingStripComponent} from '../../../../shared/components/zx-rating-strip/zx-rating-strip.component';
import {ZxCalloutComponent} from '../../../../shared/ui/zx-callout/zx-callout.component';
import {ZxAddedByComponent} from '../../../../shared/ui/zx-added-by/zx-added-by.component';
import {ZxPartyPlaceComponent} from '../../../../shared/lib/zx-party-place/zx-party-place.component';
import {ZxProdContextComponent} from '../../../../entities/zx-prod-context/components/zx-prod-context/zx-prod-context.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

import {ZxPictureViewerComponent} from '../zx-picture-viewer/zx-picture-viewer.component';
import {ZxPictureMetaPanelComponent} from '../zx-picture-meta-panel/zx-picture-meta-panel.component';
import {ZxPictureDownloadsPanelComponent} from '../zx-picture-downloads-panel/zx-picture-downloads-panel.component';
import {ZxPictureMaterialsSectionComponent} from '../zx-picture-materials-section/zx-picture-materials-section.component';
import {ZxPictureStagesSectionComponent} from '../zx-picture-stages-section/zx-picture-stages-section.component';
import {ZxPictureRelatedSectionComponent} from '../zx-picture-related-section/zx-picture-related-section.component';
import {ZxPictureEditingControlsComponent} from '../zx-picture-editing-controls/zx-picture-editing-controls.component';
import {ZxPictureDetailsSkeletonComponent} from '../zx-picture-details-skeleton/zx-picture-details-skeleton.component';

import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';


import {RouterLink} from '@angular/router';@Component({
  selector: 'zx-picture-details-view',
  standalone: true,
  imports: [RouterLink, 
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxGridComponent,
    ZxGridItemDirective,
    ZxBadgeComponent,
    TagsListComponent,
    ZxItemControlsComponent,
    ZxRatingStripComponent,
    ZxCalloutComponent,
    ZxAddedByComponent,
    ZxPartyPlaceComponent,
    ZxProdContextComponent,
    TextDirective,
    ZxPictureViewerComponent,
    ZxPictureMetaPanelComponent,
    ZxPictureDownloadsPanelComponent,
    ZxPictureMaterialsSectionComponent,
    ZxPictureStagesSectionComponent,
    ZxPictureRelatedSectionComponent,
    ZxPictureEditingControlsComponent,
    ZxPictureDetailsSkeletonComponent,
    CommentsListComponent,
    RatingsListComponent,
  ],
  templateUrl: './zx-picture-details.component.html',
  styleUrls: ['./zx-picture-details.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureDetailsComponent implements OnChanges, OnDestroy {
  @Input() elementId = 0;
  @Output() pageTitleChange = new EventEmitter<string>();

  details$: Observable<PictureDetailsDto | null> = of(null);

  private viewSubscription?: Subscription;

  constructor(
    private readonly api: PictureDetailsApiService,
    private readonly analytics: AnalyticsService,
    private readonly pageMetadataService: PageMetadataService,
    private readonly breadcrumbService: BreadcrumbService,
    private readonly translate: TranslateService,
  ) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (!changes['elementId']) {
      return;
    }

    this.viewSubscription?.unsubscribe();
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
              {title: this.translate.instant('menu.graphics'), url: '/pictures'},
              ...(details.authors.length ? [{title: details.authors[0].name, url: details.authors[0].url}] : []),
            ],
            currentTitle: details.title,
          });
        }
      }),
      shareReplay(1),
    );

    // Log the view once, after the details have loaded (replaces the legacy
    // server-side/AJAX logging) and report the tracking goal as in legacy.
    this.viewSubscription = this.details$
      .pipe(filter((details): details is PictureDetailsDto => !!details), take(1))
      .subscribe(details => {
        this.api.logView(details.id).subscribe();
        this.analytics.reachGoal('viewimage');
      });
  }

  ngOnDestroy(): void {
    this.viewSubscription?.unsubscribe();
  }
}

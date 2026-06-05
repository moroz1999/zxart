import {ChangeDetectionStrategy, Component, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of, Subscription} from 'rxjs';
import {filter, shareReplay, take} from 'rxjs/operators';
import {PictureDetailsDto} from '../../models/picture-details.dto';
import {PictureDetailsApiService} from '../../services/picture-details-api.service';
import {AnalyticsService} from '../../../../shared/services/analytics.service';

import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {TagsListComponent} from '../../../../shared/lib/tags-list/tags-list.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxAddedByComponent} from '../../../../shared/ui/zx-added-by/zx-added-by.component';
import {ZxPartyPlaceComponent} from '../../../../shared/lib/zx-party-place/zx-party-place.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
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

@Component({
  selector: 'zx-picture-details',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxBreadcrumbsComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxGridComponent,
    ZxBadgeComponent,
    TagsListComponent,
    ZxItemControlsComponent,
    ZxAddedByComponent,
    ZxPartyPlaceComponent,
    HeadingDirective,
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
export class ZxPictureDetailsComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;

  details$: Observable<PictureDetailsDto | null> = of(null);

  private viewSubscription?: Subscription;

  constructor(
    private readonly api: PictureDetailsApiService,
    private readonly analytics: AnalyticsService,
  ) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      this.details$ = of(null);
      return;
    }
    this.details$ = this.api.getDetails(+this.elementId).pipe(shareReplay(1));

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

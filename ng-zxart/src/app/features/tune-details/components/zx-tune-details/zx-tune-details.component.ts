import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {TuneDetailsDto} from '../../models/tune-details.dto';
import {TuneDetailsApiService} from '../../services/tune-details-api.service';

import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
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
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';

import {ZxTunePlayerComponent} from '../zx-tune-player/zx-tune-player.component';
import {ZxTuneMetaPanelComponent} from '../zx-tune-meta-panel/zx-tune-meta-panel.component';
import {ZxTuneDownloadsPanelComponent} from '../zx-tune-downloads-panel/zx-tune-downloads-panel.component';
import {ZxTuneRelatedSectionComponent} from '../zx-tune-related-section/zx-tune-related-section.component';
import {ZxTuneEditingControlsComponent} from '../zx-tune-editing-controls/zx-tune-editing-controls.component';
import {ZxTuneDetailsSkeletonComponent} from '../zx-tune-details-skeleton/zx-tune-details-skeleton.component';

import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';

@Component({
  selector: 'zx-tune-details',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxBreadcrumbsComponent,
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
    HeadingDirective,
    TextDirective,
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
export class ZxTuneDetailsComponent implements OnInit {
  @Input() elementId = 0;

  details$: Observable<TuneDetailsDto | null> = of(null);

  constructor(private readonly api: TuneDetailsApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      this.details$ = of(null);
      return;
    }
    this.details$ = this.api.getDetails(+this.elementId).pipe(shareReplay(1));
  }
}

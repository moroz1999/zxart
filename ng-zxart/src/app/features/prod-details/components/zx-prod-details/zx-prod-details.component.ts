import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, of} from 'rxjs';
import {
  ZxProdDetailsSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-prod-details-skeleton/zx-prod-details-skeleton.component';
import {ZxYoutubeEmbedComponent} from '../../../../shared/ui/zx-youtube-embed/zx-youtube-embed.component';
import {ProdCoreApiService} from '../../services/prod-core-api.service';
import {ProdCoreDto} from '../../models/prod-core.dto';
import {ZxProdHeroComponent} from '../zx-prod-hero/zx-prod-hero.component';
import {ZxProdEditingControlsComponent} from '../zx-prod-editing-controls/zx-prod-editing-controls.component';
import {ZxProdDescriptionComponent} from '../zx-prod-description/zx-prod-description.component';
import {ZxProdInstructionsComponent} from '../zx-prod-instructions/zx-prod-instructions.component';
import {ZxProdScreenshotsSectionComponent,} from '../zx-prod-screenshots-section/zx-prod-screenshots-section.component';
import {ZxProdReleasesSectionComponent,} from '../zx-prod-releases-section/zx-prod-releases-section.component';
import {ZxProdArticlesSectionComponent,} from '../zx-prod-articles-section/zx-prod-articles-section.component';
import {ZxProdMentionsSectionComponent,} from '../zx-prod-mentions-section/zx-prod-mentions-section.component';
import {
  ZxProdCompilationItemsSectionComponent,
} from '../zx-prod-compilation-items-section/zx-prod-compilation-items-section.component';
import {
  ZxProdSeriesProdsSectionComponent,
} from '../zx-prod-series-prods-section/zx-prod-series-prods-section.component';
import {
  ZxProdCompilationsSectionComponent,
} from '../zx-prod-compilations-section/zx-prod-compilations-section.component';
import {ZxProdSeriesSectionComponent,} from '../zx-prod-series-section/zx-prod-series-section.component';
import {ZxProdMusicSectionComponent,} from '../zx-prod-music-section/zx-prod-music-section.component';
import {ZxProdPicturesSectionComponent,} from '../zx-prod-pictures-section/zx-prod-pictures-section.component';
import {ZxProdInlaysSectionComponent,} from '../zx-prod-inlays-section/zx-prod-inlays-section.component';
import {ZxProdMapsSectionComponent,} from '../zx-prod-maps-section/zx-prod-maps-section.component';
import {ZxProdRzxSectionComponent,} from '../zx-prod-rzx-section/zx-prod-rzx-section.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxInsetComponent} from '../../../../shared/ui/zx-inset/zx-inset.component';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {TagsQuickFormComponent} from '../../../tags-quick-form/components/tags-quick-form/tags-quick-form.component';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';
import {TranslateModule} from '@ngx-translate/core';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {ZxTagsChipsComponent} from '../../../../shared/ui/zx-tags-chips/zx-tags-chips.component';

@Component({
  selector: 'zx-prod-details',
  standalone: true,
  imports: [
    CommonModule,
    ZxProdDetailsSkeletonComponent,
    ZxYoutubeEmbedComponent,
    ZxProdHeroComponent,
    ZxProdEditingControlsComponent,
    ZxProdDescriptionComponent,
    ZxProdInstructionsComponent,
    ZxProdScreenshotsSectionComponent,
    ZxProdReleasesSectionComponent,
    ZxProdArticlesSectionComponent,
    ZxProdMentionsSectionComponent,
    ZxProdCompilationItemsSectionComponent,
    ZxProdSeriesProdsSectionComponent,
    ZxProdCompilationsSectionComponent,
    ZxProdSeriesSectionComponent,
    ZxProdMusicSectionComponent,
    ZxProdPicturesSectionComponent,
    ZxProdInlaysSectionComponent,
    ZxProdMapsSectionComponent,
    ZxProdRzxSectionComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxInsetComponent,
    ZxTabsComponent,
    ZxTabComponent,
    TranslateModule,
    TagsQuickFormComponent,
    CommentsListComponent,
    RatingsListComponent,
    ZxBreadcrumbsComponent,
    ZxTagsChipsComponent,
  ],
  templateUrl: './zx-prod-details.component.html',
  styleUrls: ['./zx-prod-details.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDetailsComponent implements OnInit {
  @Input() elementId = 0;

  core$: Observable<ProdCoreDto | null> = of(null);

  constructor(private readonly api: ProdCoreApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      this.core$ = of(null);
      return;
    }
    this.core$ = this.api.getCore(+this.elementId);
  }
}

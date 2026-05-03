import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, of} from 'rxjs';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxYoutubeEmbedComponent} from '../../../../shared/ui/zx-youtube-embed/zx-youtube-embed.component';
import {ProdCoreApiService} from '../../services/prod-core-api.service';
import {ProdCoreDto} from '../../models/prod-core.dto';
import {ZxProdInfoTableComponent} from '../zx-prod-info-table/zx-prod-info-table.component';
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
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {TagsQuickFormComponent} from '../../../tags-quick-form/components/tags-quick-form/tags-quick-form.component';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';

@Component({
  selector: 'zx-prod-details',
  standalone: true,
  imports: [
    CommonModule,
    ZxSkeletonComponent,
    ZxYoutubeEmbedComponent,
    ZxProdInfoTableComponent,
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
    ZxStackComponent,
    TagsQuickFormComponent,
    CommentsListComponent,
    RatingsListComponent,
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

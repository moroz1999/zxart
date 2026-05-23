import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {ReleaseDetailsDto} from '../../models/release-details.dto';
import {ReleaseDetailsApiService} from '../../services/release-details-api.service';
import {ZxReleaseDetailsSkeletonComponent} from '../zx-release-details-skeleton/zx-release-details-skeleton.component';
import {ZxReleaseParentAnchorComponent} from '../zx-release-parent-anchor/zx-release-parent-anchor.component';
import {ZxReleaseHeroComponent} from '../zx-release-hero/zx-release-hero.component';
import {ZxReleaseActionBarComponent} from '../zx-release-action-bar/zx-release-action-bar.component';
import {ZxReleaseScreenshotsSectionComponent} from '../zx-release-screenshots-section/zx-release-screenshots-section.component';
import {ZxReleaseInlaysSectionComponent} from '../zx-release-inlays-section/zx-release-inlays-section.component';
import {ZxReleaseInstructionsSectionComponent} from '../zx-release-instructions-section/zx-release-instructions-section.component';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';
import {ZxReleaseFileStructureComponent} from '../zx-release-file-structure/zx-release-file-structure.component';

@Component({
  selector: 'zx-release-details',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxReleaseDetailsSkeletonComponent,
    ZxReleaseParentAnchorComponent,
    ZxReleaseHeroComponent,
    ZxReleaseActionBarComponent,
    ZxReleaseScreenshotsSectionComponent,
    ZxReleaseInlaysSectionComponent,
    ZxReleaseInstructionsSectionComponent,
    ZxBreadcrumbsComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    CommentsListComponent,
    RatingsListComponent,
    ZxReleaseFileStructureComponent,
  ],
  templateUrl: './zx-release-details.component.html',
  styleUrl: './zx-release-details.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseDetailsComponent implements OnInit {
  @Input() elementId = 0;

  details$: Observable<ReleaseDetailsDto | null> = of(null);

  constructor(private readonly api: ReleaseDetailsApiService) {}

  ngOnInit(): void {
    if (!this.elementId || +this.elementId <= 0) {
      this.details$ = of(null);
      return;
    }
    this.details$ = this.api.getDetails(+this.elementId).pipe(shareReplay(1));
  }

}

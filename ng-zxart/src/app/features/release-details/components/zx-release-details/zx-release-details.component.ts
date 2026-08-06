import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, Output, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay, tap} from 'rxjs/operators';
import {ReleaseDetailsDto} from '../../models/release-details.dto';
import {ReleaseDetailsApiService} from '../../services/release-details-api.service';
import {ZxReleaseDetailsSkeletonComponent} from '../zx-release-details-skeleton/zx-release-details-skeleton.component';
import {ZxReleaseHeroComponent} from '../zx-release-hero/zx-release-hero.component';
import {ZxReleaseScreenshotsSectionComponent} from '../zx-release-screenshots-section/zx-release-screenshots-section.component';
import {ZxReleaseInlaysSectionComponent} from '../zx-release-inlays-section/zx-release-inlays-section.component';
import {ZxReleaseInstructionsSectionComponent} from '../zx-release-instructions-section/zx-release-instructions-section.component';
import {BreadcrumbService} from '../../../../shared/services/breadcrumb.service';
import {ZxPreformattedComponent} from '../../../../shared/ui/zx-preformatted/zx-preformatted.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {CommentsListComponent} from '../../../comments/components/comments-list/comments-list.component';
import {RatingsListComponent} from '../../../ratings/components/ratings-list/ratings-list.component';
import {ZxReleaseFileStructureComponent} from '../zx-release-file-structure/zx-release-file-structure.component';
import {ZxProdPicturesSectionComponent} from '../../../prod-details/components/zx-prod-pictures-section/zx-prod-pictures-section.component';
import {PageMetadataService} from '../../../../shared/services/page-metadata.service';

@Component({
  selector: 'zx-release-details-view',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxReleaseDetailsSkeletonComponent,
    ZxReleaseHeroComponent,
    ZxReleaseScreenshotsSectionComponent,
    ZxReleaseInlaysSectionComponent,
    ZxReleaseInstructionsSectionComponent,
    ZxPreformattedComponent,
    ZxStackComponent,
    HeadingDirective,
    TextDirective,
    CommentsListComponent,
    RatingsListComponent,
    ZxReleaseFileStructureComponent,
    ZxProdPicturesSectionComponent,
  ],
  templateUrl: './zx-release-details.component.html',
  styleUrl: './zx-release-details.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseDetailsComponent implements OnChanges {
  @Input() elementId = 0;
  @Output() pageTitleChange = new EventEmitter<string>();

  details$: Observable<ReleaseDetailsDto | null> = of(null);

  constructor(
    private readonly api: ReleaseDetailsApiService,
    private readonly breadcrumbService: BreadcrumbService,
    private readonly translate: TranslateService,
    private readonly pageMetadata: PageMetadataService,
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
          this.pageMetadata.applyPlainTitle(details.title);
          const categories = details.prod.categoriesPaths.length ? details.prod.categoriesPaths[0].categories : [];
          this.breadcrumbService.setEntityTrail({
            items: [
              {title: this.translate.instant('menu.software'), url: '/prods'},
              ...categories.map(category => ({title: category.title, url: '/prods', queryParams: {cat: category.id}})),
              {title: details.prod.title, url: details.prod.url},
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

}

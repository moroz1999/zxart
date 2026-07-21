import {ChangeDetectionStrategy, ChangeDetectorRef, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Params} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxPictureCardComponent} from '../../../../entities/zx-picture-card/zx-picture-card.component';
import {
  ZxPictureCardSkeletonComponent
} from '../../../../entities/zx-picture-card-skeleton/zx-picture-card-skeleton.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxSortSelectComponent} from '../../../../shared/ui/zx-sort-select/zx-sort-select.component';
import {
  PictureGalleryHostComponent
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureBrowserService} from '../../services/picture-browser.service';
import {BrowserBaseComponent} from '../../../../shared/browser-base.component';
import {ZxLoadingStateDirective} from '../../../../shared/ui/zx-loading-state/zx-loading-state.directive';

@Component({
  selector: 'zx-picture-browser',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxPictureCardSkeletonComponent,
    TextDirective,
    ZxPicturesGridDirective,
    ZxPaginationComponent,
    ZxSortSelectComponent,
    PictureGalleryHostComponent,
    ZxLoadingStateDirective,
  ],
  templateUrl: './zx-picture-browser.component.html',
  styleUrls: ['./zx-picture-browser.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureBrowserComponent extends BrowserBaseComponent {
  protected override readonly itemsPerPage = 48;

  pictures: ZxPictureDto[] = [];
  readonly galleryId = 'picture-browser';
  readonly skeletonItems = [0, 1, 2, 3, 4, 5];

  /** Tag narrowing the collection; 0 browses all pictures. Comes from the `tag` query param. */
  private tagId = 0;

  constructor(
    private pictureBrowserService: PictureBrowserService,
    private pictureGalleryService: PictureGalleryService,
    translateService: TranslateService,
    cdr: ChangeDetectorRef,
  ) {
    super(translateService, cdr);
  }

  protected override onQueryParams(params: Params): void {
    this.tagId = params['tag'] ? +params['tag'] : 0;
    this.filterParams = this.tagId > 0 ? {tag: this.tagId} : {};
  }

  protected override fetchPage(start: number, limit: number): void {
    this.pictureBrowserService.getPaged(this.tagId, start, limit, this.sorting).subscribe({
      next: response => {
        this.loading = false;
        this.pictures = response.items;
        this.total = response.total;
        this.pagesAmount = Math.ceil(this.total / limit);
        this.pictureGalleryService.ensureGalleryLoaded(this.galleryId, response.items);
        this.cdr.markForCheck();
      },
      error: () => {
        this.loading = false;
        this.error = true;
        this.cdr.markForCheck();
      },
    });
  }
}

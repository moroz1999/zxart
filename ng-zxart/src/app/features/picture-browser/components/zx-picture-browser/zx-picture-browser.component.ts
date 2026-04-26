import {ChangeDetectionStrategy, ChangeDetectorRef, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {
  ZxPictureCardSkeletonComponent
} from '../../../../shared/ui/zx-picture-card-skeleton/zx-picture-card-skeleton.component';
import {ZxCaptionDirective} from '../../../../shared/directives/typography/typography.directives';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxSortSelectComponent} from '../../../../shared/ui/zx-sort-select/zx-sort-select.component';
import {
  PictureGalleryHostComponent
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureBrowserService} from '../../services/picture-browser.service';
import {BrowserBaseComponent} from '../../../../shared/browser-base.component';

@Component({
  selector: 'zx-picture-browser',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxPictureCardSkeletonComponent,
    ZxCaptionDirective,
    ZxPicturesGridDirective,
    ZxPaginationComponent,
    ZxSortSelectComponent,
    PictureGalleryHostComponent,
  ],
  templateUrl: './zx-picture-browser.component.html',
  styleUrls: ['./zx-picture-browser.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureBrowserComponent extends BrowserBaseComponent {
  protected override readonly itemsPerPage = 48;

  pictures: ZxPictureDto[] = [];
  galleryId = '';
  readonly skeletonItems = [0, 1, 2, 3, 4, 5];

  constructor(
    private pictureBrowserService: PictureBrowserService,
    private pictureGalleryService: PictureGalleryService,
    translateService: TranslateService,
    cdr: ChangeDetectorRef,
  ) {
    super(translateService, cdr);
  }

  protected override onBeforeInit(): void {
    this.galleryId = `picture-browser-${this.elementId}`;
  }

  protected override fetchPage(start: number, limit: number): void {
    this.pictureBrowserService.getPaged(this.elementId, start, limit, this.sorting).subscribe({
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

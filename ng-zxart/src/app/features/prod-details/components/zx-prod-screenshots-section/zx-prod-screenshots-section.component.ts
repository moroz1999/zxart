import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {
  PictureGalleryHostComponent,
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';
import {LightboxModule} from 'ng-gallery/lightbox';
import {ProdScreenshotsApiService} from '../../services/prod-screenshots-api.service';
import {ProdFileDto} from '../../models/prod-file.dto';
import {ZxCaptionDirective, ZxHeading2Directive,} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'zx-prod-screenshots-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxSkeletonComponent,
    PictureGalleryHostComponent,
    LightboxModule,
    ZxCaptionDirective,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-prod-screenshots-section.component.html',
  styleUrls: ['./zx-prod-screenshots-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdScreenshotsSectionComponent {
  @Input({required: true}) elementId!: number;
  @Input() titleKey = 'prod-details.screenshots';

  loading = false;
  loaded = false;
  files: ProdFileDto[] = [];
  galleryId = '';

  constructor(
    private readonly api: ProdScreenshotsApiService,
    private readonly gallery: PictureGalleryService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.galleryId = `zx-prod-screenshots-${this.elementId}`;
    this.loading = true;
    this.api.getScreenshots(this.elementId).subscribe(files => {
      this.files = files;
      this.loaded = true;
      this.loading = false;
      if (files.length) {
        this.gallery.loadItems(this.galleryId, files.map(this.toGalleryItem));
      }
      this.cdr.markForCheck();
    });
  }

  private toGalleryItem(file: ProdFileDto): PictureGalleryItem {
    return {
      id: file.id,
      title: file.title,
      thumbUrl: file.imageUrl ?? file.fullImageUrl ?? '',
      largeUrl: file.fullImageUrl ?? file.imageUrl ?? '',
      detailsUrl: file.downloadUrl,
    };
  }
}

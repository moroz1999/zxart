import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {LightboxModule} from 'ng-gallery/lightbox';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxPictureGridSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {
  PictureGalleryHostComponent,
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';
import {ZxCaptionDirective, ZxHeading2Directive,} from '../../../../shared/directives/typography/typography.directives';
import {ProdInlaysApiService} from '../../services/prod-inlays-api.service';
import {ProdFileDto} from '../../models/prod-file.dto';

@Component({
  selector: 'zx-prod-inlays-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    LightboxModule,
    InViewportDirective,
    ZxPictureGridSkeletonComponent,
    PictureGalleryHostComponent,
    ZxCaptionDirective,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-prod-inlays-section.component.html',
  styleUrls: ['./zx-prod-inlays-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInlaysSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  files: ProdFileDto[] = [];
  galleryId = '';

  constructor(
    private readonly api: ProdInlaysApiService,
    private readonly gallery: PictureGalleryService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.galleryId = `zx-prod-inlays-${this.elementId}`;
    this.loading = true;
    this.api.getInlays(this.elementId).subscribe(files => {
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

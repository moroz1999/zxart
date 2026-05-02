import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {LightboxModule} from 'ng-gallery/lightbox';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {
  PictureGalleryHostComponent,
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';
import {ZxCaptionDirective, ZxHeading2Directive,} from '../../../../shared/directives/typography/typography.directives';
import {ProdMapsApiService} from '../../services/prod-maps-api.service';
import {ProdFileDto} from '../../models/prod-file.dto';

@Component({
  selector: 'zx-prod-maps-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    LightboxModule,
    InViewportDirective,
    ZxSkeletonComponent,
    ZxButtonComponent,
    PictureGalleryHostComponent,
    ZxCaptionDirective,
    ZxHeading2Directive,
  ],
  templateUrl: './zx-prod-maps-section.component.html',
  styleUrls: ['./zx-prod-maps-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdMapsSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  files: ProdFileDto[] = [];
  mapsUrl: string | null = null;
  galleryId = '';

  constructor(
    private readonly api: ProdMapsApiService,
    private readonly gallery: PictureGalleryService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  get hasContent(): boolean {
    return this.files.length > 0 || !!this.mapsUrl;
  }

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.galleryId = `zx-prod-maps-${this.elementId}`;
    this.loading = true;
    this.api.getMaps(this.elementId).subscribe(payload => {
      this.files = payload.files ?? [];
      this.mapsUrl = payload.mapsUrl ?? null;
      this.loaded = true;
      this.loading = false;
      if (this.files.length) {
        this.gallery.loadItems(this.galleryId, this.files.map(this.toGalleryItem));
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

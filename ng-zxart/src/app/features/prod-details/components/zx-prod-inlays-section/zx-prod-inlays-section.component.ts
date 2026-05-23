import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Lightbox} from 'ng-gallery/lightbox';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxPictureGridSkeletonComponent,
} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {
  PictureGalleryHostComponent,
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {ProdInlaysApiService} from '../../services/prod-inlays-api.service';
import {ProdReleaseInlayDto} from '../../models/prod-release-inlay.dto';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlayTileComponent} from '../../../../shared/ui/zx-inlay-tile/zx-inlay-tile.component';

@Component({
  selector: 'zx-prod-inlays-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxPictureGridSkeletonComponent,
    PictureGalleryHostComponent,
    HeadingDirective,
    ZxStackComponent,
    ZxInlayTileComponent,
  ],
  templateUrl: './zx-prod-inlays-section.component.html',
  styleUrls: ['./zx-prod-inlays-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdInlaysSectionComponent {
  @Input({required: true}) elementId!: number;

  loading = false;
  loaded = false;
  inlays: ProdReleaseInlayDto[] = [];
  galleryId = '';

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.inlays.length === 0 ? 'none' : '';
  }

  constructor(
    private readonly api: ProdInlaysApiService,
    private readonly gallery: PictureGalleryService,
    private readonly lightbox: Lightbox,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.galleryId = `zx-prod-inlays-${this.elementId}`;
    this.loading = true;
    this.api.getInlays(this.elementId).subscribe(inlays => {
      this.inlays = inlays;
      this.loaded = true;
      this.loading = false;
      if (inlays.length) {
        this.gallery.loadItems(this.galleryId, inlays.map(this.toGalleryItem));
      }
      this.cdr.markForCheck();
    });
  }

  openAt(index: number): void {
    this.lightbox.open(index, this.galleryId);
  }

  trackById(_: number, inlay: ProdReleaseInlayDto): number {
    return inlay.id;
  }

  private toGalleryItem(inlay: ProdReleaseInlayDto): PictureGalleryItem {
    return {
      id: inlay.id,
      title: inlay.title,
      thumbUrl: inlay.imageUrl ?? inlay.fullImageUrl ?? '',
      largeUrl: inlay.fullImageUrl ?? inlay.imageUrl ?? '',
      detailsUrl: inlay.downloadUrl,
    };
  }
}

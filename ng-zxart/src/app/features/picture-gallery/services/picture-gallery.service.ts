import {Injectable, signal, TemplateRef} from '@angular/core';
import {Gallery, GalleryConfig} from 'ng-gallery';
import {Lightbox} from 'ng-gallery/lightbox';
import {mapGalleryItemToImageItem, mapPictureToGalleryItem} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {PictureGalleryItem} from '../models/picture-gallery-item';
import {AnalyticsService} from '../../../shared/services/analytics.service';

@Injectable({
  providedIn: 'root'
})
export class PictureGalleryService {
  readonly zoomEnabled = signal(false);

  private imageTemplate: TemplateRef<unknown> | null = null;
  /** Gallery shown in the lightbox, which the slides' arrows turn. */
  private openGalleryId: string | null = null;

  constructor(
    private gallery: Gallery,
    private lightbox: Lightbox,
    private analytics: AnalyticsService,
  ) {
    this.lightbox.opened.subscribe((galleryId: string) => {
      this.openGalleryId = galleryId;
      this.analytics.reachGoal('viewimage');
    });
    this.lightbox.closed.subscribe(() => {
      this.zoomEnabled.set(false);
    });
  }

  registerImageTemplate(template: TemplateRef<unknown>): void {
    this.imageTemplate = template;
  }

  ensureGalleryLoaded(galleryId: string, pictures: readonly ZxPictureDto[]): void {
    if (!pictures.length) {
      return;
    }
    this.applyConfig(galleryId, true);
    const items = pictures
      .map(mapPictureToGalleryItem)
      .map(mapGalleryItemToImageItem);
    this.gallery.ref(galleryId).load(items);
  }

  loadItems(galleryId: string, items: readonly PictureGalleryItem[]): void {
    if (!items.length) {
      return;
    }
    this.applyConfig(galleryId, false);
    this.gallery.ref(galleryId).load(items.map(mapGalleryItemToImageItem));
  }

  showNext(): void {
    if (this.openGalleryId !== null) {
      this.gallery.ref(this.openGalleryId).next();
    }
  }

  showPrevious(): void {
    if (this.openGalleryId !== null) {
      this.gallery.ref(this.openGalleryId).prev();
    }
  }

  toggleZoom(): void {
    this.zoomEnabled.update(value => !value);
  }

  /**
   * A picture slide draws its own arrows inside the picture block, so the info
   * beside it is never covered; galleries of other images keep the stock ones.
   */
  private applyConfig(galleryId: string, pictureSlides: boolean): void {
    const config: GalleryConfig = {
      imageTemplate: this.imageTemplate ?? undefined,
      thumbPosition: 'right',
      nav: !pictureSlides,
    };
    this.gallery.ref(galleryId).setConfig(config);
  }
}

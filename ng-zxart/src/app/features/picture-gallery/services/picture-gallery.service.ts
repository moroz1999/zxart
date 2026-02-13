import {Injectable, signal, TemplateRef} from '@angular/core';
import {Gallery, GalleryConfig} from 'ng-gallery';
import {Lightbox} from 'ng-gallery/lightbox';
import {mapGalleryItemToImageItem, mapPictureToGalleryItem} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

const GALLERY_ID_PREFIX = 'zx-picture-lightbox';

@Injectable({
  providedIn: 'root'
})
export class PictureGalleryService {
  readonly zoomEnabled = signal(false);

  private imageTemplate: TemplateRef<unknown> | null = null;

  constructor(
    private gallery: Gallery,
    private lightbox: Lightbox,
  ) {
    this.lightbox.closed.subscribe(id => {
      if (id.startsWith(GALLERY_ID_PREFIX)) {
        this.zoomEnabled.set(false);
      }
    });
  }

  registerImageTemplate(template: TemplateRef<unknown>): void {
    this.imageTemplate = template;
  }

  ensureGalleryLoaded(galleryId: string, pictures: readonly ZxPictureDto[]): void {
    if (!pictures.length) {
      return;
    }
    this.applyConfig(galleryId);
    const items = pictures
      .map(mapPictureToGalleryItem)
      .map(mapGalleryItemToImageItem);
    this.gallery.ref(galleryId).load(items);
  }

  toggleZoom(): void {
    this.zoomEnabled.update(value => !value);
  }

  private applyConfig(galleryId: string): void {
    const config: GalleryConfig = {
      imageTemplate: this.imageTemplate ?? undefined,
    };
    this.gallery.ref(galleryId).setConfig(config);
  }
}

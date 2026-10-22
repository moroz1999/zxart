import {Injectable} from '@angular/core';
import {Dialog} from '@angular/cdk/dialog';
import {mapPictureToGalleryItem} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {PictureGalleryItem} from '../models/picture-gallery-item';
import {PictureGalleryDialogData} from '../models/picture-gallery-dialog-data';
import {AnalyticsService} from '../../../shared/services/analytics.service';
import {GalleryOpener} from '../../../shared/models/gallery-opener';

/**
 * Registry of the galleries a page offers, by id, and the opener of the
 * fullscreen gallery. Lists register their pictures, sections their images;
 * a click opens the gallery at the image clicked.
 */
@Injectable({
  providedIn: 'root'
})
export class PictureGalleryService implements GalleryOpener {
  private readonly galleries = new Map<string, readonly PictureGalleryItem[]>();

  constructor(
    private readonly dialog: Dialog,
    private readonly analytics: AnalyticsService,
  ) {}

  ensureGalleryLoaded(galleryId: string, pictures: readonly ZxPictureDto[]): void {
    if (!pictures.length) {
      return;
    }
    this.galleries.set(galleryId, pictures.map(mapPictureToGalleryItem));
  }

  loadItems(galleryId: string, items: readonly PictureGalleryItem[]): void {
    if (!items.length) {
      return;
    }
    this.galleries.set(galleryId, [...items]);
  }

  async open(galleryId: string, index: number): Promise<void> {
    const items = this.galleries.get(galleryId);
    if (items === undefined) {
      return;
    }
    // the gallery and its loupe library load only when a gallery is opened
    const {PictureGalleryDialogComponent} = await import('../components/picture-gallery-dialog/picture-gallery-dialog.component');
    const data: PictureGalleryDialogData = {items, startIndex: index};
    this.dialog.open(PictureGalleryDialogComponent, {
      data,
      panelClass: 'zx-picture-gallery-panel',
      backdropClass: 'zx-picture-gallery-backdrop',
      autoFocus: 'dialog',
    });
    this.analytics.reachGoal('viewimage');
  }
}

import {PictureGalleryItem} from './picture-gallery-item';

export interface PictureGalleryDialogData {
  readonly items: readonly PictureGalleryItem[];
  readonly startIndex: number;
}

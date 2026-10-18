import {PictureGalleryInfo} from './picture-gallery-info';

export interface PictureGalleryItem {
  readonly id: number;
  readonly title: string;
  readonly thumbUrl: string;
  readonly largeUrl: string;
  readonly detailsUrl: string;
  /** Present for pictures; other images (screenshots, maps) show no info bar. */
  readonly info?: PictureGalleryInfo;
}

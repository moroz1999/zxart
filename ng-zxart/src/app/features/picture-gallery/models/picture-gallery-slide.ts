import {PictureGalleryInfo} from './picture-gallery-info';

/** What one slide of the gallery shows. */
export interface PictureGallerySlide {
  /** Image drawn on the slide at a whole multiple of its native size. */
  readonly src: string;
  /** Larger render the loupe magnifies. */
  readonly zoomSrc: string;
  readonly alt: string;
  /** Present for pictures; other images (screenshots, maps) show no info. */
  readonly info: PictureGalleryInfo | null;
}

import {ImageItem, ImageItemData} from 'ng-gallery';
import {PictureGalleryItem} from '../models/picture-gallery-item';
import {PictureGalleryInfo} from '../models/picture-gallery-info';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

export function mapPictureToGalleryItem(picture: ZxPictureDto): PictureGalleryItem {
  return {
    id: picture.id,
    title: picture.title,
    thumbUrl: picture.imageUrl,
    largeUrl: picture.largeImageUrl,
    detailsUrl: picture.url,
    info: {
      pictureId: picture.id,
      title: picture.title,
      detailsUrl: picture.url,
      authors: picture.authors,
      format: picture.type,
      votes: picture.votes,
      votesAmount: picture.votesAmount,
      userVote: picture.userVote,
      denyVoting: picture.denyVoting,
    },
  };
}

/**
 * Data of a picture slide. The picture stays an ordinary image item: the
 * gallery's own image loads `src`, which keeps the slide lifecycle the slider
 * relies on, and the image template draws the slide over it. `src` is the
 * native-size render the slide scales.
 */
export interface PictureGallerySlideData extends ImageItemData {
  readonly src: string;
  /** Larger render the zoom lens magnifies. */
  readonly zoomSrc: string;
  readonly info: PictureGalleryInfo;
}

export function mapGalleryItemToImageItem(item: PictureGalleryItem): ImageItem {
  if (item.info !== undefined) {
    const data: PictureGallerySlideData = {
      src: item.thumbUrl,
      zoomSrc: item.largeUrl,
      thumb: item.thumbUrl,
      alt: item.title,
      info: item.info,
    };
    return new ImageItem(data);
  }
  return new ImageItem({
    src: item.largeUrl,
    thumb: item.thumbUrl,
    alt: item.title,
  });
}

import {PictureGalleryItem} from '../models/picture-gallery-item';
import {PictureGallerySlide} from '../models/picture-gallery-slide';
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
 * A picture is shown from its native-size render (the thumbnail URL renders at
 * zoom 1) and magnified from the larger one. Other images, such as screenshots,
 * are shown from their full image.
 */
export function toGallerySlide(item: PictureGalleryItem): PictureGallerySlide {
  if (item.info !== undefined) {
    return {src: item.thumbUrl, zoomSrc: item.largeUrl, alt: item.title, info: item.info};
  }
  return {src: item.largeUrl, zoomSrc: item.largeUrl, alt: item.title, info: null};
}

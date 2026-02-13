import {ImageItem} from 'ng-gallery';
import {PictureGalleryItem} from '../models/picture-gallery-item';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

export function mapPictureToGalleryItem(picture: ZxPictureDto): PictureGalleryItem {
  const largeUrl = picture.imageLargeUrl ?? picture.imageUrl;
  return {
    id: picture.id,
    title: picture.title,
    thumbUrl: picture.imageUrl,
    largeUrl,
    detailsUrl: picture.url,
  };
}

export function mapGalleryItemToImageItem(item: PictureGalleryItem): ImageItem {
  return new ImageItem({
    src: item.largeUrl,
    thumb: item.thumbUrl,
    alt: item.title,
  });
}

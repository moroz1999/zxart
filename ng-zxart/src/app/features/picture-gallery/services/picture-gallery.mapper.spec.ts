import {mapPictureToGalleryItem} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

describe('pictureGalleryMapper', () => {
  const basePicture: ZxPictureDto = {
    id: 42,
    title: 'Picture',
    url: '/pictures/42/',
    imageUrl: '/thumb.png',
    year: null,
    authors: [],
    party: null,
    isRealtime: false,
    isFlickering: false,
    compo: null,
    votes: 0,
    votesAmount: 0,
    userVote: null,
    denyVoting: false,
    commentsAmount: 0,
  };

  it('uses large image url when provided', () => {
    const item = mapPictureToGalleryItem({
      ...basePicture,
      imageLargeUrl: '/large.png',
    });

    expect(item.largeUrl).toBe('/large.png');
  });

  it('falls back to imageUrl when large url is missing', () => {
    const item = mapPictureToGalleryItem(basePicture);

    expect(item.largeUrl).toBe('/thumb.png');
  });
});

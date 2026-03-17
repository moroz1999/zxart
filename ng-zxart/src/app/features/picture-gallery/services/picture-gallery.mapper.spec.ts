import {mapPictureToGalleryItem} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

describe('pictureGalleryMapper', () => {
  const basePicture: ZxPictureDto = {
    id: 42,
    title: 'Picture',
    url: '/pictures/42/',
    imageUrl: '/thumb.png',
    fileId: 1,
    type: 'standard',
    pictureBorder: 1,
    palette: 'srgb',
    rotation: null,
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

  it('uses imageUrl for both thumb and large', () => {
    const item = mapPictureToGalleryItem(basePicture);

    expect(item.thumbUrl).toBe('/thumb.png');
    expect(item.largeUrl).toBe('/thumb.png');
  });
});

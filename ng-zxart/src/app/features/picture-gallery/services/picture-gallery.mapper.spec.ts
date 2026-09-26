import {mapPictureToGalleryItem} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {describe, expect, it, vi} from 'vitest';

vi.mock('ng-gallery', () => ({
  ImageItem: class {},
}));

describe('pictureGalleryMapper', () => {
  const basePicture: ZxPictureDto = {
    id: 42,
    title: 'Picture',
    url: '/pictures/42/',
    imageUrl: '/thumb.png',
    largeImageUrl: '/large.png',
    fileId: 1,
    type: 'standard',
    pictureBorder: 1,
    palette: 'srgb',
    rotation: null,
    year: null,
    authors: [],
    party: null,
    release: null,
    isRealtime: false,
    isFlickering: false,
    compo: null,
    votes: 0,
    votesAmount: 0,
    userVote: null,
    denyVoting: false,
    commentsAmount: 0,
    views: 0,
  };

  it('maps the thumbnail and large image URLs', () => {
    const item = mapPictureToGalleryItem(basePicture);

    expect(item.thumbUrl).toBe('/thumb.png');
    expect(item.largeUrl).toBe('/large.png');
  });
});

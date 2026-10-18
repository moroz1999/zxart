import {mapGalleryItemToImageItem, mapPictureToGalleryItem, PictureGallerySlideData} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ImageItem} from 'ng-gallery';
import {describe, expect, it, vi} from 'vitest';

vi.mock('ng-gallery', () => ({
  ImageItem: class {
    constructor(readonly data: unknown) {}
  },
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

  it('carries what the gallery shows about the picture and its rating', () => {
    const item = mapPictureToGalleryItem({
      ...basePicture,
      type: 'gigascreen',
      authors: [{name: 'Dimidrol', url: '/author/1'}],
      votes: 4.2,
      votesAmount: 17,
      userVote: 5,
    });

    expect(item.info).toEqual({
      pictureId: 42,
      title: 'Picture',
      detailsUrl: '/pictures/42/',
      authors: [{name: 'Dimidrol', url: '/author/1'}],
      format: 'gigascreen',
      votes: 4.2,
      votesAmount: 17,
      userVote: 5,
      denyVoting: false,
    });
  });

  it('keeps a picture an ordinary image item, loaded from its native-size image', () => {
    // the gallery's own image item drives the slide lifecycle the slider relies on
    const slide = mapGalleryItemToImageItem(mapPictureToGalleryItem(basePicture));
    const data = slide.data as PictureGallerySlideData;

    expect(slide).toBeInstanceOf(ImageItem);

    expect(data.src).toBe('/thumb.png');
    expect(data.zoomSrc).toBe('/large.png');
    expect(data.info.pictureId).toBe(42);
  });
});

import {describe, expect, it} from 'vitest';
import {mapPictureToGalleryItem, toGallerySlide} from './picture-gallery.mapper';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {PictureGalleryItem} from '../models/picture-gallery-item';

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

  it('shows a picture from its native-size render and zooms into the larger one', () => {
    const slide = toGallerySlide(mapPictureToGalleryItem(basePicture));

    expect(slide.src).toBe('/thumb.png');
    expect(slide.zoomSrc).toBe('/large.png');
    expect(slide.info?.pictureId).toBe(42);
  });

  it('shows another image, such as a screenshot, from its full image without info', () => {
    const screenshot: PictureGalleryItem = {id: 1, title: 'Shot', thumbUrl: '/small.png', largeUrl: '/full.png', detailsUrl: ''};

    const slide = toGallerySlide(screenshot);

    expect(slide.src).toBe('/full.png');
    expect(slide.zoomSrc).toBe('/full.png');
    expect(slide.info).toBeNull();
  });
});

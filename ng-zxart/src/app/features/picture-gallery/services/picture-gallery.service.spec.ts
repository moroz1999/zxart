import {Subject} from 'rxjs';
import {describe, expect, it, vi} from 'vitest';
import {Gallery} from 'ng-gallery';
import {Lightbox} from 'ng-gallery/lightbox';
import {PictureGalleryService} from './picture-gallery.service';
import {AnalyticsService} from '../../../shared/services/analytics.service';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

vi.mock('ng-gallery', () => ({ImageItem: class {}, Gallery: class {}}));
vi.mock('ng-gallery/lightbox', () => ({Lightbox: class {}}));

function createRef() {
  const ref = {
    next: vi.fn(),
    prev: vi.fn(),
    setConfig: vi.fn(),
    load: vi.fn(),
  };
  return ref;
}

function createService() {
  const refs = new Map<string, ReturnType<typeof createRef>>();
  const gallery = {
    ref: (id: string) => {
      if (!refs.has(id)) {
        refs.set(id, createRef());
      }
      return refs.get(id)!;
    },
  };
  const opened = new Subject<string>();
  const lightbox = {opened, closed: new Subject<string>()};
  const service = new PictureGalleryService(
    gallery as unknown as Gallery,
    lightbox as unknown as Lightbox,
    {reachGoal: vi.fn()} as unknown as AnalyticsService,
  );
  return {service, refs, opened};
}

describe('PictureGalleryService', () => {
  it('turns pages of the gallery that is open', () => {
    const {service, refs, opened} = createService();
    opened.next('pictures-1');

    service.showNext();
    service.showPrevious();

    expect(refs.get('pictures-1')!.next).toHaveBeenCalled();
    expect(refs.get('pictures-1')!.prev).toHaveBeenCalled();
  });

  it('leaves the arrows of a picture gallery to its slides', () => {
    const {service, refs} = createService();

    service.ensureGalleryLoaded('pictures-1', [{id: 1} as ZxPictureDto]);

    expect(refs.get('pictures-1')!.setConfig).toHaveBeenCalledWith(expect.objectContaining({nav: false}));
  });
});

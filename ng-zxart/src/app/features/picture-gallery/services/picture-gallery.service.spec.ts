import {Dialog} from '@angular/cdk/dialog';
import {describe, expect, it, vi} from 'vitest';
import {PictureGalleryService} from './picture-gallery.service';
import {AnalyticsService} from '../../../shared/services/analytics.service';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {PictureGalleryItem} from '../models/picture-gallery-item';

vi.mock('../components/picture-gallery-dialog/picture-gallery-dialog.component', () => ({
  PictureGalleryDialogComponent: class {},
}));

function createService() {
  const dialog = {open: vi.fn()};
  const analytics = {reachGoal: vi.fn()};
  const service = new PictureGalleryService(dialog as unknown as Dialog, analytics as unknown as AnalyticsService);
  return {service, dialog, analytics};
}

const SCREENSHOT: PictureGalleryItem = {id: 1, title: 'Shot', thumbUrl: '/t.png', largeUrl: '/l.png', detailsUrl: ''};

describe('PictureGalleryService', () => {
  it('opens a registered gallery at the picture asked for', async () => {
    const {service, dialog, analytics} = createService();
    service.loadItems('shots', [SCREENSHOT, {...SCREENSHOT, id: 2}]);

    await service.open('shots', 1);

    expect(dialog.open).toHaveBeenCalledTimes(1);
    expect(dialog.open.mock.calls[0][1].data).toEqual({items: [SCREENSHOT, {...SCREENSHOT, id: 2}], startIndex: 1});
    expect(analytics.reachGoal).toHaveBeenCalledWith('viewimage');
  });

  it('registers a list of pictures with their info', async () => {
    const {service, dialog} = createService();
    service.ensureGalleryLoaded('pictures', [{id: 7, title: 'Capri', url: '/picture/7', imageUrl: '/z1', largeImageUrl: '/z2', authors: [], type: 'standard', votes: 0, votesAmount: 0, userVote: null, denyVoting: false} as unknown as ZxPictureDto]);

    await service.open('pictures', 0);

    const items = dialog.open.mock.calls[0][1].data.items as PictureGalleryItem[];
    expect(items[0].info?.pictureId).toBe(7);
  });

  it('opens nothing for a gallery that was never loaded', async () => {
    const {service, dialog} = createService();

    await service.open('missing', 0);

    expect(dialog.open).not.toHaveBeenCalled();
  });
});

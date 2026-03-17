import {Injectable} from '@angular/core';
import {PictureSettings} from '../../features/picture-settings/models/picture-settings';

export interface PictureUrlParams {
  fileId: number;
  type: string;
  pictureBorder: number;
  palette: string;
  rotation: number | null;
  isFlickering: boolean;
}

@Injectable({
  providedIn: 'root',
})
export class PictureUrlBuilderService {
  private readonly BASE_PATH = '/zximages/';

  buildUrl(params: PictureUrlParams, settings: PictureSettings, zoom: 1 | 2 = 1): string {
    const parts: string[] = [];
    parts.push(`id=${params.fileId}`);

    if (params.rotation) {
      parts.push(`rotation=${params.rotation}`);
    }

    if (settings.border || settings.hidden) {
      parts.push(`border=${params.pictureBorder}`);
    }

    if (params.isFlickering) {
      parts.push(`mode=${settings.mode}`);
    }

    if (params.palette) {
      parts.push(`pal=${params.palette}`);
    }

    const type = settings.hidden && params.type === 'standard' ? 'hidden' : params.type;
    parts.push(`type=${type}`);
    parts.push(`zoom=${zoom}`);

    return this.BASE_PATH + parts.join(';');
  }
}

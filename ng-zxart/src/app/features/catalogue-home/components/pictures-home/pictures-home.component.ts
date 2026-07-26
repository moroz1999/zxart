import {ChangeDetectionStrategy, Component} from '@angular/core';
import {BestPicturesOfMonthComponent} from '../../../firstpage/modules/best-pictures-of-month/best-pictures-of-month.component';
import {NewPicturesComponent} from '../../../firstpage/modules/new-pictures/new-pictures.component';
import {UnvotedPicturesComponent} from '../../../firstpage/modules/unvoted-pictures/unvoted-pictures.component';
import {MODULE_SETTINGS} from '../../../firstpage/models/module-settings.token';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

@Component({
  selector: 'zx-pictures-home',
  standalone: true,
  imports: [
    ZxStackComponent,
    PictureGalleryHostComponent,
    NewPicturesComponent,
    UnvotedPicturesComponent,
    BestPicturesOfMonthComponent,
  ],
  providers: [
    {provide: MODULE_SETTINGS, useValue: {limit: 12}},
  ],
  templateUrl: './pictures-home.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PicturesHomeComponent {}

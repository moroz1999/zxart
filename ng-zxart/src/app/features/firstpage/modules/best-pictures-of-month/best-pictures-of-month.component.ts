import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable, tap} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';

const GALLERY_ID = 'zx-picture-lightbox-best-pictures-of-month';

@Component({
  selector: 'zx-fp-best-pictures-of-month',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxPictureCardComponent],
  templateUrl: './best-pictures-of-month.component.html',
  styleUrls: ['./best-pictures-of-month.component.scss']
})
export class BestPicturesOfMonthComponent extends FirstpageModuleBase<ZxPictureDto> {
  readonly moduleType = 'bestPicturesOfMonth' as const;

  constructor(
    private dataService: FirstpageDataService,
    private pictureGalleryService: PictureGalleryService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxPictureDto[]> {
    return this.dataService.getBestPicturesOfMonth(this.settings.limit).pipe(
      tap(items => this.pictureGalleryService.ensureGalleryLoaded(GALLERY_ID, items)),
    );
  }
}

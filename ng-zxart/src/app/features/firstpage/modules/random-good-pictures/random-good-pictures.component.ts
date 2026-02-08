import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';

@Component({
  selector: 'zx-fp-random-good-pictures',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxPictureCardComponent],
  templateUrl: './random-good-pictures.component.html',
  styleUrls: ['./random-good-pictures.component.scss']
})
export class RandomGoodPicturesComponent extends FirstpageModuleBase<ZxPictureDto> {
  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxPictureDto[]> {
    return this.dataService.getRandomGoodPictures(this.settings.limit);
  }
}

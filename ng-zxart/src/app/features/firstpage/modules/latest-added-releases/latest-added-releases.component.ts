import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxReleaseDto} from '../../../../shared/models/zx-release-dto';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ZxReleaseItemComponent} from '../../../../shared/ui/zx-release-item/zx-release-item.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';

@Component({
  selector: 'zx-fp-latest-added-releases',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxReleaseItemComponent],
  templateUrl: './latest-added-releases.component.html',
})
export class LatestAddedReleasesComponent extends FirstpageModuleBase<ZxReleaseDto> {
  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxReleaseDto[]> {
    return this.dataService.getLatestAddedReleases(this.settings.limit);
  }
}

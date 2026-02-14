import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ZxProdBlockComponent} from '../../../../shared/ui/zx-prod-block/zx-prod-block.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';

@Component({
  selector: 'zx-fp-latest-added-releases',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxProdBlockComponent],
  templateUrl: './latest-added-releases.component.html',
  styleUrls: ['./latest-added-releases.component.scss'],
})
export class LatestAddedReleasesComponent extends FirstpageModuleBase<ZxProd> {
  readonly moduleType = 'latestAddedReleases' as const;

  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxProd[]> {
    return this.dataService.getLatestAddedReleases(this.settings.limit);
  }
}

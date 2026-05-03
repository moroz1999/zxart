import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';

@Component({
  selector: 'zx-fp-latest-added-prods',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxProdsListComponent],
  templateUrl: './latest-added-prods.component.html',
  styleUrls: ['./latest-added-prods.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LatestAddedProdsComponent extends FirstpageModuleBase<ZxProd> {
  readonly moduleType = 'latestAddedProds' as const;

  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxProd[]> {
    return this.dataService.getLatestAddedProds(this.settings.limit);
  }
}

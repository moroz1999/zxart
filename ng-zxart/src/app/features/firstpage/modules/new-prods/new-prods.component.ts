import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ZxProdBlockComponent} from '../../../../zx-prod-block/zx-prod-block.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';

@Component({
  selector: 'zx-fp-new-prods',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxProdBlockComponent],
  templateUrl: './new-prods.component.html',
  styleUrls: ['./new-prods.component.scss']
})
export class NewProdsComponent extends FirstpageModuleBase<ZxProd> {
  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxProd[]> {
    return this.dataService.getNewProds(this.settings.limit, this.settings.minRating ?? 0);
  }
}

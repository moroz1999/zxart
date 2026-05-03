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
  selector: 'zx-fp-support-prods',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxProdsListComponent],
  templateUrl: './support-prods.component.html',
  styleUrls: ['./support-prods.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SupportProdsComponent extends FirstpageModuleBase<ZxProd> {
  readonly moduleType = 'supportProds' as const;

  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxProd[]> {
    return this.dataService.getSupportProds(this.settings.limit);
  }
}

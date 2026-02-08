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
  selector: 'zx-fp-best-new-games',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxProdBlockComponent],
  templateUrl: './best-new-games.component.html',
  styleUrls: ['./best-new-games.component.scss']
})
export class BestNewGamesComponent extends FirstpageModuleBase<ZxProd> {
  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxProd[]> {
    return this.dataService.getBestNewGames(this.settings.limit, this.settings.minRating ?? 4);
  }
}

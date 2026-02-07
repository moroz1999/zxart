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
  selector: 'app-fp-new-prods',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxProdBlockComponent],
  template: `
    <app-firstpage-module-wrapper
      titleKey="firstpage.modules.newProds"
      [usePanel]="false"
      [loading]="loading"
      [error]="error"
      [empty]="items.length === 0"
      skeletonVariant="prod-grid"
      [skeletonCount]="settings.limit"
    >
      <div class="prods-grid">
        <app-zx-prod-block *ngFor="let prod of items" [model]="prod"></app-zx-prod-block>
      </div>
    </app-firstpage-module-wrapper>
  `,
  styles: [`.prods-grid { display: grid; grid-template-columns: repeat(auto-fill, 256px); justify-content: space-evenly; gap: var(--space-16); }`]
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

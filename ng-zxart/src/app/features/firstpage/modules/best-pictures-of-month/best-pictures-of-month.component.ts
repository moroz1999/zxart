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
  selector: 'app-fp-best-pictures-of-month',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxPictureCardComponent],
  template: `
    <app-firstpage-module-wrapper
      titleKey="firstpage.modules.bestPicturesOfMonth"
      [usePanel]="false"
      [loading]="loading"
      [error]="error"
      [empty]="items.length === 0"
      skeletonVariant="picture-grid"
      [skeletonCount]="settings.limit"
    >
      <div class="pictures-grid">
        <zx-picture-card *ngFor="let pic of items" [picture]="pic"></zx-picture-card>
      </div>
    </app-firstpage-module-wrapper>
  `,
  styles: [`.pictures-grid { display: grid; grid-template-columns: repeat(auto-fill, 320px); justify-content: space-evenly; gap: var(--space-16); }`]
})
export class BestPicturesOfMonthComponent extends FirstpageModuleBase<ZxPictureDto> {
  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ZxPictureDto[]> {
    return this.dataService.getBestPicturesOfMonth(this.settings.limit);
  }
}

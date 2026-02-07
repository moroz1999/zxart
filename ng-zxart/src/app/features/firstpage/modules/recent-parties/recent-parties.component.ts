import {Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {PartyDto} from '../../../../shared/models/party-dto';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ZxPartyCardComponent} from '../../../../shared/ui/zx-party-card/zx-party-card.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';

@Component({
  selector: 'app-fp-recent-parties',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxPartyCardComponent],
  template: `
    <app-firstpage-module-wrapper
      titleKey="firstpage.modules.recentParties"
      [loading]="loading"
      [error]="error"
      [empty]="items.length === 0"
      skeletonVariant="row"
      [skeletonCount]="settings.limit"
    >
      <zx-party-card *ngFor="let party of items" [party]="party"></zx-party-card>
    </app-firstpage-module-wrapper>
  `
})
export class RecentPartiesComponent extends FirstpageModuleBase<PartyDto> {
  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<PartyDto[]> {
    return this.dataService.getRecentParties(this.settings.limit);
  }
}

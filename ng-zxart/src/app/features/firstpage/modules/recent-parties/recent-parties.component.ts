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
  selector: 'zx-fp-recent-parties',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxPartyCardComponent],
  templateUrl: './recent-parties.component.html',
  styleUrls: ['./recent-parties.component.scss']
})
export class RecentPartiesComponent extends FirstpageModuleBase<PartyDto> {
  readonly moduleType = 'recentParties' as const;

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

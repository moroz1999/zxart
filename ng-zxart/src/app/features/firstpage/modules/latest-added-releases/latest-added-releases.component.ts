import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {CommonModule} from '@angular/common';
import {Observable} from 'rxjs';
import {FirstpageModuleBase} from '../firstpage-module.base';
import {FirstpageDataService} from '../../services/firstpage-data.service';
import {
  FirstpageModuleWrapperComponent
} from '../../components/firstpage-module-wrapper/firstpage-module-wrapper.component';
import {ModuleSettings} from '../../models/firstpage-config';
import {MODULE_SETTINGS} from '../../models/module-settings.token';
import {ProdReleaseDto} from '../../../prod-details/models/prod-release.dto';
import {ZxProdReleaseCardComponent} from '../../../../entities/zx-prod-release-card/zx-prod-release-card.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxProdsGridDirective} from '../../../../shared/directives/prods-grid.directive';

@Component({
  selector: 'zx-fp-latest-added-releases',
  standalone: true,
  imports: [CommonModule, FirstpageModuleWrapperComponent, ZxProdReleaseCardComponent, ZxProdsListSkeletonComponent, ZxProdsGridDirective],
  templateUrl: './latest-added-releases.component.html',
  styleUrls: ['./latest-added-releases.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class LatestAddedReleasesComponent extends FirstpageModuleBase<ProdReleaseDto> {
  readonly moduleType = 'latestAddedReleases' as const;

  constructor(
    private dataService: FirstpageDataService,
    @Inject(MODULE_SETTINGS) settings: ModuleSettings,
  ) {
    super(settings);
  }

  protected loadData(): Observable<ProdReleaseDto[]> {
    return this.dataService.getLatestAddedReleases(this.settings.limit);
  }
}

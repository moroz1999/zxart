import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {catchError, map, startWith} from 'rxjs/operators';
import {ZxManageTabsComponent} from '../../features/manage/components/zx-manage-tabs/zx-manage-tabs.component';
import {ManageUsersStatsDto} from '../../features/manage-users/models/manage-users.dto';
import {ManageUsersApiService} from '../../features/manage-users/services/manage-users-api.service';
import {
  StatsBarDataset,
  ZxStatsBarChartComponent,
} from '../../features/stats/components/zx-stats-bar-chart/zx-stats-bar-chart.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {ZxGridComponent} from '../../shared/ui/zx-grid/zx-grid.component';
import {ZxPageLayoutComponent} from '../../shared/ui/zx-page-layout/zx-page-layout.component';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxSpinnerComponent} from '../../shared/ui/zx-spinner/zx-spinner.component';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';

interface StatTile {
  labelKey: string;
  value: number;
}

interface UserStatsVm {
  loading: boolean;
  error: boolean;
  tiles: StatTile[];
  chartLabels: string[];
  chartDatasets: StatsBarDataset[];
}

/**
 * The users dashboard (`/manage/user-stats`): how many users there are, how
 * many came recently, how many are banned or never confirmed their email, and
 * the registrations of the last twelve months.
 */
@Component({
  selector: 'zx-manage-user-stats-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    HeadingDirective,
    TextDirective,
    ZxGridComponent,
    ZxManageTabsComponent,
    ZxPageLayoutComponent,
    ZxPanelComponent,
    ZxSpinnerComponent,
    ZxStackComponent,
    ZxStatsBarChartComponent,
  ],
  templateUrl: './manage-user-stats-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ManageUserStatsPageComponent {
  readonly vm$: Observable<UserStatsVm> = this.api.getStats().pipe(
    map(stats => this.buildVm(stats)),
    catchError(() => of(this.emptyVm(false, true))),
    startWith(this.emptyVm(true, false)),
  );

  constructor(private readonly api: ManageUsersApiService) {}

  trackByLabel(_index: number, tile: StatTile): string {
    return tile.labelKey;
  }

  private buildVm(stats: ManageUsersStatsDto): UserStatsVm {
    return {
      loading: false,
      error: false,
      tiles: [
        {labelKey: 'manage-users.stats-total', value: stats.total},
        {labelKey: 'manage-users.stats-new-week', value: stats.newLastWeek},
        {labelKey: 'manage-users.stats-new-month', value: stats.newLastMonth},
        {labelKey: 'manage-users.stats-banned', value: stats.banned},
        {labelKey: 'manage-users.stats-unverified', value: stats.unverified},
      ],
      chartLabels: stats.registrationsByMonth.map(month => month.month),
      chartDatasets: [{
        label: '',
        data: stats.registrationsByMonth.map(month => month.count),
        color: getComputedStyle(document.documentElement).getPropertyValue('--zx-stats-chart-series-primary').trim(),
      }],
    };
  }

  private emptyVm(loading: boolean, error: boolean): UserStatsVm {
    return {loading, error, tiles: [], chartLabels: [], chartDatasets: []};
  }
}

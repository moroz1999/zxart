import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {
  ZxSkeletonBoneComponent,
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {StatsService} from '../../services/stats.service';
import {ZxStatsCategoryComponent} from '../zx-stats-category/zx-stats-category.component';
import {ZxStatsUsersComponent} from '../zx-stats-users/zx-stats-users.component';

@Component({
  selector: 'zx-stats',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTabsComponent,
    ZxTabComponent,
    ZxTabContentDirective,
    ZxPanelComponent,
    ZxGridComponent,
    ZxStackComponent,
    ZxSkeletonBoneComponent,
    ZxStatsCategoryComponent,
    ZxStatsUsersComponent,
  ],
  templateUrl: './zx-stats.component.html',
  styleUrl: './zx-stats.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsComponent {
  readonly overview$ = this.statsService.overview$;
  readonly kpiPlaceholders = [0, 1, 2, 3, 4, 5];

  constructor(private readonly statsService: StatsService) {}
}

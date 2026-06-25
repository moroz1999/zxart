import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxStatsSectionSkeletonComponent} from '../zx-stats-section-skeleton/zx-stats-section-skeleton.component';

@Component({
  selector: 'zx-stats-tab-skeleton',
  standalone: true,
  imports: [CommonModule, ZxGridComponent, ZxStackComponent, ZxStatsSectionSkeletonComponent],
  templateUrl: './zx-stats-tab-skeleton.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsTabSkeletonComponent {
  @Input() variant: 'category' | 'users' = 'category';

  readonly categoryDistributionPlaceholders = [0, 1, 2];
  readonly usersPlaceholders = [0, 1, 2];
}

import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ZxStatsComponent} from '../../features/stats/components/zx-stats/zx-stats.component';

/** Routed statistics entrypoint (`/stats`). */
@Component({
  selector: 'zx-stats-page',
  standalone: true,
  imports: [ZxStatsComponent],
  template: '<zx-stats></zx-stats>',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class StatsPageComponent {}

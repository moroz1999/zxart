import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxMedalComponent, ZxMedalVariant} from '../../../../shared/ui/zx-medal/zx-medal.component';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {StatsTopUser} from '../../models/stats.models';

@Component({
  selector: 'zx-stats-top-table',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxTableComponent, ZxMedalComponent, ZxBadgeComponent],
  templateUrl: './zx-stats-top-table.component.html',
  styleUrl: './zx-stats-top-table.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsTopTableComponent {
  @Input() title = '';
  @Input() unitKey = '';
  @Input() users: StatsTopUser[] = [];

  medalVariant(index: number): ZxMedalVariant | null {
    return [null, 'gold', 'silver', 'bronze'][index + 1] as ZxMedalVariant | null;
  }
}

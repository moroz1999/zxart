import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxTableComponent} from '../../../../shared/ui/zx-table/zx-table.component';
import {ZxMedalComponent, ZxMedalVariant} from '../../../../shared/ui/zx-medal/zx-medal.component';
import {ZxUserComponent} from '../../../../entities/zx-user/zx-user.component';
import {StatsTopUser} from '../../models/stats.models';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {StatsNumberPipe} from '../../pipes/stats-number.pipe';

@Component({
  selector: 'zx-stats-top-table',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxMedalComponent,
    ZxUserComponent,
    TextDirective,
    StatsNumberPipe,
  ],
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

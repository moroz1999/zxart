import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {map, startWith} from 'rxjs/operators';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {StatsUsersSection} from '../../models/stats.models';
import {StatsService} from '../../services/stats.service';
import {ZxStatsTopTableComponent} from '../zx-stats-top-table/zx-stats-top-table.component';
import {ZxStatsSectionSkeletonComponent} from '../zx-stats-section-skeleton/zx-stats-section-skeleton.component';

@Component({
  selector: 'zx-stats-users',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxGridComponent,
    ZxStatsTopTableComponent,
    ZxStatsSectionSkeletonComponent,
  ],
  templateUrl: './zx-stats-users.component.html',
  styleUrl: './zx-stats-users.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStatsUsersComponent implements OnInit {
  section$!: Observable<StatsUsersSection | null>;
  loaded$!: Observable<boolean>;

  constructor(private readonly statsService: StatsService) {}

  ngOnInit(): void {
    this.section$ = this.statsService.getUsers();
    this.loaded$ = this.section$.pipe(map(section => section !== null), startWith(false));
  }
}

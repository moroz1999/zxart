import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {GroupListItem} from '../../features/group-browser/models/group-list-item';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';
import {ZxLoadingStateDirective} from '../../shared/ui/zx-loading-state/zx-loading-state.directive';
import {RouterLink} from '@angular/router';

@Component({
  selector: 'zx-groups-table',
  standalone: true,
  imports: [RouterLink,
    CommonModule,
    TranslateModule,
    ZxTableComponent,
    ZxLoadingStateDirective,
  ],
  templateUrl: './zx-groups-table.component.html',
  styleUrls: ['./zx-groups-table.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupsTableComponent {
  @Input() groups: GroupListItem[] = [];
  @Input() rowStartIndex = 0;
  @Input() loading = false;
  @Input() showRowNumbers = true;
}

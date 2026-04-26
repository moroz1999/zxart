import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {GroupListItem} from '../../../features/group-browser/models/group-list-item';
import {ZxTableComponent} from '../zx-table/zx-table.component';

@Component({
  selector: 'zx-groups-table',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTableComponent,
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

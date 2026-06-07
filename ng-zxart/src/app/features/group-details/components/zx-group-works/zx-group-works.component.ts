import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {GroupCoreDto} from '../../models/group-core.dto';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabContentDirective} from '../../../../shared/ui/zx-tabs/zx-tab-content.directive';
import {ZxGroupProdsTabComponent} from '../zx-group-prods-tab/zx-group-prods-tab.component';

@Component({
  selector: 'zx-group-works',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxTabsComponent,
    ZxTabComponent,
    ZxTabContentDirective,
    ZxGroupProdsTabComponent,
  ],
  templateUrl: './zx-group-works.component.html',
  styleUrl: './zx-group-works.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupWorksComponent {
  @Input() core!: GroupCoreDto;
  @Input() urlBase = '';
}

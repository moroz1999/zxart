import {ChangeDetectionStrategy, Component, Input, OnChanges} from '@angular/core';
import {GroupCoreDto} from '../../models/group-core.dto';
import {GroupProdsScope} from '../../services/group-prods-api.service';
import {ZxGroupProdsTabComponent} from '../zx-group-prods-tab/zx-group-prods-tab.component';

/**
 * Works of a group as one list filtered by the role the group had, rather than
 * a tab per role. Roles the group has nothing in are left out, and `all` only
 * appears once there is more than one role to merge.
 */
@Component({
  selector: 'zx-group-works',
  standalone: true,
  imports: [ZxGroupProdsTabComponent],
  templateUrl: './zx-group-works.component.html',
  styleUrl: './zx-group-works.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupWorksComponent implements OnChanges {
  @Input() core!: GroupCoreDto;

  scopes: GroupProdsScope[] = ['own'];

  ngOnChanges(): void {
    const roles: GroupProdsScope[] = [];
    if (this.core?.tabs.hasProds) {
      roles.push('own');
    }
    if (this.core?.tabs.hasPublished) {
      roles.push('published');
    }
    if (this.core?.tabs.hasReleases) {
      roles.push('releases');
    }

    this.scopes = roles.length > 1 ? ['all', ...roles] : roles;
  }
}

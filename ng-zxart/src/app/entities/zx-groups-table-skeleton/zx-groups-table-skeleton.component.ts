import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';
import {
  ZxSkeletonBoneComponent
} from '../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxSkeletonVisibilityDirective} from '../../shared/ui/zx-skeleton/zx-skeleton-visibility.directive';

@Component({
  selector: 'zx-groups-table-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [TranslateModule, ZxTableComponent, ZxSkeletonBoneComponent],
  templateUrl: './zx-groups-table-skeleton.component.html',
  styleUrl: './zx-groups-table-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxGroupsTableSkeletonComponent {
  @Input() count = 8;
  @Input() showRowNumbers = true;

  get rows(): number[] {
    return Array.from({length: this.count}, (_, index) => index);
  }
}

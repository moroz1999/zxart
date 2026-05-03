import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

const SEARCH_GROUP_ITEMS = 5;

@Component({
  selector: 'zx-search-groups-skeleton',
  standalone: true,
  imports: [ZxSkeletonBoneComponent],
  templateUrl: './zx-search-groups-skeleton.component.html',
  styleUrls: ['./zx-search-groups-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSearchGroupsSkeletonComponent {
  @Input() count = 5;
  @Input() animated = true;

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }

  get groupItems(): number[] {
    return Array.from({length: SEARCH_GROUP_ITEMS}, (_, i) => i);
  }
}

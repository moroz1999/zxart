import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-row-skeleton',
  standalone: true,
  imports: [ZxSkeletonBoneComponent],
  templateUrl: './zx-row-skeleton.component.html',
  styleUrls: ['./zx-row-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxRowSkeletonComponent {
  @Input() count = 5;
  @Input() animated = true;

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }
}

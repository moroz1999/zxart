import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-text-skeleton',
  standalone: true,
  imports: [ZxSkeletonBoneComponent],
  templateUrl: './zx-text-skeleton.component.html',
  styleUrls: ['./zx-text-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTextSkeletonComponent {
  @Input() count = 5;
  @Input() animated = true;
  @Input() lineHeight = '16px';

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }
}

import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-picture-grid-skeleton',
  standalone: true,
  imports: [ZxSkeletonBoneComponent],
  templateUrl: './zx-picture-grid-skeleton.component.html',
  styleUrls: ['./zx-picture-grid-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureGridSkeletonComponent {
  @Input() count = 5;
  @Input() animated = true;

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }
}

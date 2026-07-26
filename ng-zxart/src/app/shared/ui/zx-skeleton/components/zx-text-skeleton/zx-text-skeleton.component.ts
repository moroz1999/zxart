import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-text-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
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

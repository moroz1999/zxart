import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';

@Component({
  selector: 'zx-skeleton-bone',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  templateUrl: './zx-skeleton-bone.component.html',
  styleUrls: ['./zx-skeleton-bone.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSkeletonBoneComponent {
  @Input() animated = true;

  @HostBinding('class.animated')
  get animatedClass(): boolean {
    return this.animated;
  }
}

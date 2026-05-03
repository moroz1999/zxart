import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-skeleton-bone',
  standalone: true,
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

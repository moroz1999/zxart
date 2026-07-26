import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-comment-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [ZxSkeletonBoneComponent],
  templateUrl: './zx-comment-skeleton.component.html',
  styleUrls: ['./zx-comment-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCommentSkeletonComponent {
  @Input() count = 5;
  @Input() animated = true;

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }
}

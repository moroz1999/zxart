import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxScreenshotsGridDirective} from '../../../../directives/screenshots-grid.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

/**
 * Placeholder for a grid of screenshot thumbnails. Lays itself out with the
 * shared screenshots grid, and gives every bone the thumbnail aspect ratio, so
 * the placeholder cells occupy the same tracks as the real thumbnails at every
 * breakpoint.
 */
@Component({
  selector: 'zx-screenshot-grid-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective, ZxScreenshotsGridDirective],
  imports: [ZxSkeletonBoneComponent],
  templateUrl: './zx-screenshot-grid-skeleton.component.html',
  styleUrls: ['./zx-screenshot-grid-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxScreenshotGridSkeletonComponent {
  @Input() count = 5;
  @Input() animated = true;

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }
}

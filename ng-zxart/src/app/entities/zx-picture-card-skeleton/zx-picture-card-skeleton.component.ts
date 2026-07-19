import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';

@Component({
  selector: 'zx-picture-card-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  templateUrl: './zx-picture-card-skeleton.component.html',
  styleUrls: ['./zx-picture-card-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureCardSkeletonComponent {
  @Input() animated = true;
  @Input() animationDelayMs = 0;
}

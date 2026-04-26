import {ChangeDetectionStrategy, Component, Input} from '@angular/core';

@Component({
  selector: 'zx-picture-card-skeleton',
  standalone: true,
  templateUrl: './zx-picture-card-skeleton.component.html',
  styleUrls: ['./zx-picture-card-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureCardSkeletonComponent {
  @Input() animated = true;
  @Input() animationDelayMs = 0;
}

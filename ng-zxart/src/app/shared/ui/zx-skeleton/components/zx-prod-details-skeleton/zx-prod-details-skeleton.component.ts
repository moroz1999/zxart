import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxScreenshotGridSkeletonComponent} from '../zx-screenshot-grid-skeleton/zx-screenshot-grid-skeleton.component';
import {ZxProdsListSkeletonComponent} from '../zx-prods-list-skeleton/zx-prods-list-skeleton.component';

@Component({
  selector: 'zx-prod-details-skeleton',
  standalone: true,
  imports: [
    ZxSkeletonBoneComponent,
    ZxScreenshotGridSkeletonComponent,
    ZxProdsListSkeletonComponent,
  ],
  templateUrl: './zx-prod-details-skeleton.component.html',
  styleUrls: ['./zx-prod-details-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDetailsSkeletonComponent {
  @Input() animated = true;

  readonly controls = [0, 1, 2];
  readonly infoRows = Array.from({length: 12}, (_, i) => i);
  readonly descriptionLines = [0, 1, 2];
}

import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';

@Component({
  selector: 'zx-tune-details-skeleton',
  standalone: true,
  imports: [ZxSkeletonBoneComponent, ZxStackComponent, ZxGridComponent],
  templateUrl: './zx-tune-details-skeleton.component.html',
  styleUrls: ['./zx-tune-details-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTuneDetailsSkeletonComponent {}

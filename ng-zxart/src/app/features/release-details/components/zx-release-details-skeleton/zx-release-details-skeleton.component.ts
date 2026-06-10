import {ChangeDetectionStrategy, Component} from '@angular/core';
import {
  ZxSkeletonBoneComponent,
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxBreadcrumbsComponent} from '../../../../shared/ui/zx-breadcrumbs/zx-breadcrumbs.component';

@Component({
  selector: 'zx-release-details-skeleton',
  standalone: true,
  imports: [
    ZxSkeletonBoneComponent,
    ZxStackComponent,
    ZxBreadcrumbsComponent,
  ],
  templateUrl: './zx-release-details-skeleton.component.html',
  styleUrl: './zx-release-details-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxReleaseDetailsSkeletonComponent {
  readonly metaLines = [0, 1, 2];
  readonly descLines = [0, 1, 2, 3];
}

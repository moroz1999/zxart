import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from '../../../../shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {
  ZxSkeletonBoneComponent,
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxSidebarLayoutComponent} from '../../../../shared/ui/zx-sidebar-layout/zx-sidebar-layout.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

/** Loading placeholder mirroring the press article read view. */
@Component({
  selector: 'zx-press-details-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [
    ZxPanelComponent,
    ZxSkeletonBoneComponent,
    ZxSidebarLayoutComponent,
    ZxStackComponent,
  ],
  templateUrl: './zx-press-details-skeleton.component.html',
  styleUrls: ['./zx-press-details-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPressDetailsSkeletonComponent {
  readonly tocLines = [0, 1, 2, 3, 4];
  readonly mentionRows = [0, 1, 2];
  readonly contentLines = [0, 1, 2, 3, 4, 5, 6, 7];
}

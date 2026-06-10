import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxScreenshotGridSkeletonComponent} from '../zx-screenshot-grid-skeleton/zx-screenshot-grid-skeleton.component';
import {ZxRowSkeletonComponent} from '../zx-row-skeleton/zx-row-skeleton.component';
import {ZxStackComponent} from '../../../zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../zx-inline/zx-inline.component';
import {ZxPanelComponent} from '../../../zx-panel/zx-panel.component';
import {ZxBreadcrumbsComponent} from '../../../zx-breadcrumbs/zx-breadcrumbs.component';
import {ZxGridComponent} from '../../../zx-grid/zx-grid.component';
import {ZxGridItemDirective} from '../../../zx-grid/zx-grid-item.directive';
import {ZxCommentSkeletonComponent} from '../zx-comment-skeleton/zx-comment-skeleton.component';

@Component({
  selector: 'zx-prod-details-skeleton',
  standalone: true,
  imports: [
    ZxSkeletonBoneComponent,
    ZxScreenshotGridSkeletonComponent,
    ZxRowSkeletonComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxPanelComponent,
    ZxBreadcrumbsComponent,
    ZxGridComponent,
    ZxGridItemDirective,
    ZxCommentSkeletonComponent,
  ],
  templateUrl: './zx-prod-details-skeleton.component.html',
  styleUrls: ['./zx-prod-details-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdDetailsSkeletonComponent {
  @Input() animated = true;

  readonly chips = [0, 1, 2];
  readonly descriptionLines = [0, 1, 2];
  readonly links = [0, 1];
  readonly peopleLines = [0, 1];
  readonly tabs = [0, 1, 2, 3];
  readonly tags = [0, 1, 2, 3];
}

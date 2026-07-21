import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {
  ZxSkeletonBoneComponent,
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {
  ZxSkeletonVisibilityDirective,
} from '../../../../shared/ui/zx-skeleton/zx-skeleton-visibility.directive';

type SkeletonItemSize = 'small' | 'medium' | 'large' | 'extra-large';

/** Bone sizes are cycled through this pattern so the cloud looks irregular. */
const SKELETON_ITEM_PATTERN: readonly SkeletonItemSize[] = [
  'medium',
  'small',
  'large',
  'medium',
  'extra-large',
  'small',
  'large',
  'medium',
  'small',
  'extra-large',
  'medium',
  'large',
  'small',
  'medium',
];

/** Matches the size of a loaded cloud, so the page does not jump when tags arrive. */
const SKELETON_ITEM_COUNT = 200;

@Component({
  selector: 'zx-tags-cloud-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [ZxInlineComponent, ZxSkeletonBoneComponent],
  templateUrl: './zx-tags-cloud-skeleton.component.html',
  styleUrl: './zx-tags-cloud-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTagsCloudSkeletonComponent {
  readonly items: SkeletonItemSize[] = Array.from(
    {length: SKELETON_ITEM_COUNT},
    (_, index) => SKELETON_ITEM_PATTERN[index % SKELETON_ITEM_PATTERN.length],
  );
}

import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {NgForOf} from '@angular/common';
import {ZxSkeletonVisibilityDirective} from '../../../../shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxSkeletonBoneComponent} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';

/**
 * Placeholder for `zx-picture-related-section` while its rails load. Mirrors the
 * rail grid and the thumbnail rows, sized from the tokens the section itself
 * uses.
 */
@Component({
  selector: 'zx-picture-related-section-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [NgForOf, ZxSkeletonBoneComponent, ZxGridComponent, ZxPanelComponent, ZxStackComponent, ZxInlineComponent],
  templateUrl: './zx-picture-related-section-skeleton.component.html',
  styleUrls: ['./zx-picture-related-section-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureRelatedSectionSkeletonComponent {
  /** One per rail kind the section requests. */
  @Input() railCount = 3;
  @Input() itemCount = 4;
  @Input() animated = true;

  get rails(): number[] {
    return this.range(this.railCount);
  }

  get items(): number[] {
    return this.range(this.itemCount);
  }

  private range(length: number): number[] {
    return Array.from({length}, (_, i) => i);
  }
}

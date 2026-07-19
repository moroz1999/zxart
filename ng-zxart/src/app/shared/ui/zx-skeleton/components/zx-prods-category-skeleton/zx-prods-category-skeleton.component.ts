import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';
import {ZxProdsListSkeletonComponent} from '../zx-prods-list-skeleton/zx-prods-list-skeleton.component';

/**
 * First-load placeholder for the prods catalogue page (`zx-prods-category`).
 * Mirrors the page chrome — title, presets row, filter rail and selectors bar —
 * and delegates the card grid to `zx-prods-list-skeleton`.
 */
@Component({
  selector: 'zx-prods-category-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [ZxSkeletonBoneComponent, ZxProdsListSkeletonComponent],
  templateUrl: './zx-prods-category-skeleton.component.html',
  styleUrls: ['./zx-prods-category-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdsCategorySkeletonComponent {
  @Input() animated = true;
  @Input() cardCount = 12;

  readonly presets = this.range(4);
  readonly filters = this.range(7);
  readonly selectors = this.range(5);

  private range(length: number): number[] {
    return Array.from({length}, (_, i) => i);
  }
}
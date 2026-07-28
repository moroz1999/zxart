import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxProdsGridDirective} from '../../../../directives/prods-grid.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

/**
 * Placeholder for a grid of `zx-prod-block` cards. Lays itself out with the
 * shared prods grid so the placeholder cards occupy the same tracks as the real
 * cards at every breakpoint.
 */
@Component({
  selector: 'zx-prods-list-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective, ZxProdsGridDirective],
  imports: [ZxSkeletonBoneComponent],
  templateUrl: './zx-prods-list-skeleton.component.html',
  styleUrls: ['./zx-prods-list-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdsListSkeletonComponent {
  @Input() count = 5;
  @Input() animated = true;

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }
}

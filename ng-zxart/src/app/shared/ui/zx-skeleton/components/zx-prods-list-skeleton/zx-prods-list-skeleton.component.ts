import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxSkeletonVisibilityDirective} from 'src/app/shared/ui/zx-skeleton/zx-skeleton-visibility.directive';
import {ZxSkeletonBoneComponent} from '../zx-skeleton-bone/zx-skeleton-bone.component';

@Component({
  selector: 'zx-prods-list-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
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

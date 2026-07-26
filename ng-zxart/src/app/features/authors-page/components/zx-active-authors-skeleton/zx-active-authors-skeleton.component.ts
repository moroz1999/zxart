import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {
  ZxSkeletonBoneComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-skeleton-bone/zx-skeleton-bone.component';
import {
  ZxSkeletonVisibilityDirective
} from '../../../../shared/ui/zx-skeleton/zx-skeleton-visibility.directive';

@Component({
  selector: 'zx-active-authors-skeleton',
  standalone: true,
  hostDirectives: [ZxSkeletonVisibilityDirective],
  imports: [CommonModule, TextDirective, ZxSkeletonBoneComponent],
  templateUrl: './zx-active-authors-skeleton.component.html',
  styleUrl: './zx-active-authors-skeleton.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxActiveAuthorsSkeletonComponent {
  @Input() rows = 15;

  get items(): number[] {
    return Array.from({length: this.rows * 7}, (_, index) => index);
  }
}

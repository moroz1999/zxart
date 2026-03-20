import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';

export type SkeletonVariant = 'card' | 'comment' | 'row' | 'text' | 'prod-grid' | 'picture-grid' | 'tune-table';

@Component({
  selector: 'zx-skeleton',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './zx-skeleton.component.html',
  styleUrls: ['./zx-skeleton.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxSkeletonComponent {
  @Input() variant: SkeletonVariant = 'card';
  @Input() count = 5;
  @Input() animated = true;
  @Input() lineHeight = '16px';

  get items(): number[] {
    return Array.from({length: this.count}, (_, i) => i);
  }
}

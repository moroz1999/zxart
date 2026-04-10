import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';

export type ZxTableSize = 'xs' | 'sm' | 'md';

@Component({
  selector: 'zx-table',
  standalone: true,
  imports: [ZxPanelComponent],
  templateUrl: './zx-table.component.html',
  styleUrls: ['./zx-table.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxTableComponent {
  @Input() title = '';
  @Input() titleLevel: 'h2' | 'h3' = 'h3';
  @Input() size: ZxTableSize = 'sm';

  @HostBinding('class') get sizeClass(): string {
    return `zx-table-${this.size}`;
  }
}

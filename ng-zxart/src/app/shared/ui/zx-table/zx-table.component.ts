import {Component, Input} from '@angular/core';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';

@Component({
  selector: 'zx-table',
  standalone: true,
  imports: [ZxPanelComponent],
  templateUrl: './zx-table.component.html',
  styleUrls: ['./zx-table.component.scss']
})
export class ZxTableComponent {
  @Input() title = '';
  @Input() titleLevel: 'h2' | 'h3' = 'h3';
}

import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';

@Component({
  selector: 'zx-item-data',
  standalone: true,
  imports: [ZxPanelComponent],
  templateUrl: './zx-item-data.component.html',
  styleUrls: ['./zx-item-data.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxItemDataComponent {}

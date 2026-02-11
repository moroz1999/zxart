import {AfterContentInit, Component, ElementRef, Input} from '@angular/core';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';

@Component({
  selector: 'zx-table',
  standalone: true,
  imports: [ZxPanelComponent],
  templateUrl: './zx-table.component.html',
  styleUrls: ['./zx-table.component.scss']
})
export class ZxTableComponent implements AfterContentInit {
  @Input() title = '';
  @Input() titleLevel: 'h2' | 'h3' = 'h3';

  constructor(private el: ElementRef<HTMLElement>) {}

  ngAfterContentInit(): void {
    const table = this.el.nativeElement.querySelector('table');
    if (table) {
      table.classList.add('zx-table');
    }
  }
}

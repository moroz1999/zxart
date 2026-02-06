import {AfterContentInit, Component, ElementRef, Input} from '@angular/core';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';
import {ZxHeading3Directive} from "../../directives/typography/typography.directives";

@Component({
  selector: 'zx-table',
  standalone: true,
  imports: [ZxPanelComponent, ZxHeading3Directive],
  templateUrl: './zx-table.component.html',
  styleUrls: ['./zx-table.component.scss']
})
export class ZxTableComponent implements AfterContentInit {
  @Input() title = '';

  constructor(private el: ElementRef<HTMLElement>) {}

  ngAfterContentInit(): void {
    const table = this.el.nativeElement.querySelector('table');
    if (table) {
      table.classList.add('zx-table');
    }
  }
}

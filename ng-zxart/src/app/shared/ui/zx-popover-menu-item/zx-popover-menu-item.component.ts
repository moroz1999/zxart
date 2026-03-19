import {Component, HostBinding, Input} from '@angular/core';

@Component({
  selector: 'zx-popover-menu-item',
  standalone: true,
  imports: [],
  templateUrl: './zx-popover-menu-item.component.html',
  styleUrls: ['./zx-popover-menu-item.component.scss'],
})
export class ZxPopoverMenuItemComponent {
  @Input() href = '';
  @Input() active = false;
  @HostBinding('class') hostClass = 'zx-popover-menu-item';
  @HostBinding('class.active') get isActive(): boolean { return this.active; }
}

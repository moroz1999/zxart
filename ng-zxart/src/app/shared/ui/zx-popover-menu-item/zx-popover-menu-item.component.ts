import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {NgTemplateOutlet} from '@angular/common';
import {RouterLink} from '@angular/router';
import {isSpaUrl} from '../../utils/spa-url';

@Component({
  selector: 'zx-popover-menu-item',
  standalone: true,
  imports: [RouterLink, NgTemplateOutlet],
  templateUrl: './zx-popover-menu-item.component.html',
  styleUrls: ['./zx-popover-menu-item.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPopoverMenuItemComponent {
  @Input() href = '';
  @Input() queryParams: Record<string, string | number> | null = null;
  @Input() active = false;
  @HostBinding('class') hostClass = 'zx-popover-menu-item';
  @HostBinding('class.active') get isActive(): boolean { return this.active; }

  get isInternal(): boolean {
    return isSpaUrl(this.href);
  }
}

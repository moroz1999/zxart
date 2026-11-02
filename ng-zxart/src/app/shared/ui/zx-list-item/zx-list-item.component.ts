import {ChangeDetectionStrategy, Component, EventEmitter, HostBinding, Input, Output} from '@angular/core';
import {TextDirective} from '../typography/directives/text.directive';

/**
 * A row of a list the user picks from: a title, an optional second line, and
 * room on the right for the row's own actions (projected content). The row
 * itself is a native button, which its picking contract needs; the projected
 * actions sit beside it, not inside.
 */
@Component({
  selector: 'zx-list-item',
  standalone: true,
  imports: [TextDirective],
  templateUrl: './zx-list-item.component.html',
  styleUrl: './zx-list-item.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxListItemComponent {
  @Input() title = '';
  @Input() subtitle = '';
  @Input() active = false;
  @Output() picked = new EventEmitter<void>();

  @HostBinding('class') get hostClass(): string {
    return this.active ? 'zx-list-item zx-list-item--active' : 'zx-list-item';
  }
}

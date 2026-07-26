import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';

/**
 * The one close / dismiss / remove control for the whole app: a native
 * `<button>` with an inline SVG "×" (no icon-registry dependency). Use it for
 * chip removes, dialog/drawer/popover closes, cleared inputs, etc. so they all
 * look and behave identically.
 *
 * - `size` — `sm` (chips), `md` (default), `lg` (dialog headers).
 * - `round` — persistent circular backing (for closes floating over content /
 *   media); without it the backing only appears on hover.
 *
 * Emits `(closed)` with the originating `MouseEvent` (bind and stop propagation
 * yourself when the button sits inside another clickable element).
 */
@Component({
  selector: 'zx-close-button',
  standalone: true,
  templateUrl: './zx-close-button.component.html',
  styleUrl: './zx-close-button.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxCloseButtonComponent {
  @Input() size: 'sm' | 'md' | 'lg' = 'md';
  @Input() round = false;
  @Input() disabled = false;
  @Input() ariaLabel = '';
  @Output() readonly closed = new EventEmitter<MouseEvent>();

  onClick(event: MouseEvent): void {
    this.closed.emit(event);
  }
}

import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component, EventEmitter, HostBinding, Input, Output} from '@angular/core';
import {ChipItem} from '../../models/chip-item';
import {ZxCloseButtonComponent} from '../zx-close-button/zx-close-button.component';

/** Chip fill: `default` (subtle grey, for white/surface backgrounds) or
 * `surface` (raised white, so chips read against a tinted/deep container). */
export type ZxChipsVariant = 'default' | 'surface';

/**
 * Horizontal list of chips for any selected / related items (tags, groups,
 * publishers, languages, categories, …) — not tags specifically. Each chip is
 * plain text, or a link when its `url` is set, and optionally removable.
 */
@Component({
  selector: 'zx-chips',
  standalone: true,
  imports: [
    CommonModule,
    ZxCloseButtonComponent,
  ],
  templateUrl: './zx-chips.component.html',
  styleUrl: './zx-chips.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxChipsComponent {
  @Input() items: ReadonlyArray<ChipItem> = [];
  @Input() removable = false;
  @Input() disabled = false;
  @Input() removeAriaLabel = '';
  @Input() variant: ZxChipsVariant = 'default';
  @Output() removed = new EventEmitter<ChipItem>();

  @HostBinding('class.zx-chips--surface')
  get isSurface(): boolean {
    return this.variant === 'surface';
  }

  onRemove(item: ChipItem): void {
    this.removed.emit(item);
  }
}

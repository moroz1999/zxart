import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {NgForOf} from '@angular/common';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxInlineComponent} from '../zx-inline/zx-inline.component';
import {ZxNavChip} from './nav-chip';

/**
 * Horizontal strip of chips (A–Z letters, years, …) with one marked active.
 * A chip with `href` renders as a link; otherwise it is a button that emits
 * its `value` through `(chipSelect)` — used for in-page filters.
 */
@Component({
  selector: 'zx-nav-chips',
  standalone: true,
  imports: [NgForOf, ZxButtonComponent, ZxInlineComponent],
  templateUrl: './zx-nav-chips.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxNavChipsComponent {
  @Input() chips: ZxNavChip[] = [];
  @Input() size: 'xs' | 'sm' | 'md' = 'sm';
  @Input() square = false;
  @Output() chipSelect = new EventEmitter<string>();

  onClick(chip: ZxNavChip): void {
    if (chip.href === undefined) {
      this.chipSelect.emit(chip.value ?? chip.label);
    }
  }

  /** Stable identity so a fresh `chips` array (rebuilt each CD by callers) reuses DOM nodes instead of tearing them down mid-click. */
  trackChip(_index: number, chip: ZxNavChip): string {
    return chip.href ?? chip.value ?? chip.label;
  }
}

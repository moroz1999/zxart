import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

/**
 * Groups several zx-form-field children onto a single row. Spans the full width
 * of the section grid (like a `fullWidth` field) and lays its children out as
 * equal-width columns, collapsing to a single stacked column on narrow screens.
 * Use for compact related inputs that read best side by side (e.g. party / place
 * / compo). `columns` sets how many equal tracks the row uses (defaults to the
 * number of fields you place in it — set it explicitly when they differ).
 */
@Component({
  selector: 'zx-form-row',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-form-row.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormRowComponent {
  @Input() columns = 3;

  @HostBinding('style.--zx-form-row-columns') get columnsVar(): string {
    return String(this.columns);
  }
}

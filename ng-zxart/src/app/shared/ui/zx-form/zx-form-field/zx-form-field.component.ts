import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

/**
 * One form row: zx-form-label (first child) + zx-form-control (second child).
 * Layout (vertical/horizontal) is controlled by the parent zxForm directive classes.
 * `fullWidth` makes the field span every column of a two-column section grid.
 */
@Component({
  selector: 'zx-form-field',
  standalone: true,
  templateUrl: './zx-form-field.component.html',
  styleUrl: './zx-form-field.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormFieldComponent {
  @Input() fullWidth = false;

  @HostBinding('class.zx-form-field--full') get isFullWidth(): boolean {
    return this.fullWidth;
  }
}

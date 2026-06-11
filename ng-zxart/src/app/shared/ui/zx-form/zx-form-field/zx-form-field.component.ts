import {ChangeDetectionStrategy, Component} from '@angular/core';

/**
 * One form row: zx-form-label (first child) + zx-form-control (second child).
 * Layout (vertical/horizontal) is controlled by the parent zxForm directive classes.
 */
@Component({
  selector: 'zx-form-field',
  standalone: true,
  templateUrl: './zx-form-field.component.html',
  styleUrl: './zx-form-field.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormFieldComponent {
}

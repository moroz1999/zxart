import {ChangeDetectionStrategy, Component} from '@angular/core';

/**
 * Dumb layout wrapper for the control cell of a zx-form-field.
 * No context, no binding — only the flex container the field cell needs.
 */
@Component({
  selector: 'zx-form-control',
  standalone: true,
  templateUrl: './zx-form-control.component.html',
  styleUrl: './zx-form-control.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormControlComponent {
}

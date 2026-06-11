import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TextDirective} from '../../typography/directives/text.directive';

/**
 * Label cell of a zx-form-field. Renders a native <label> for accessibility;
 * `for` is forwarded to the label element.
 * Typography comes from the --zx-form-label-* component variables.
 */
@Component({
  selector: 'zx-form-label',
  standalone: true,
  imports: [TextDirective],
  templateUrl: './zx-form-label.component.html',
  styleUrl: './zx-form-label.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormLabelComponent {
  @Input() for?: string;
  @Input() required = false;
}

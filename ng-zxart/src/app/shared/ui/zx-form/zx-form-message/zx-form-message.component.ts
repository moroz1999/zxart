import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {TextDirective} from '../../typography/directives/text.directive';

export type ZxFormMessageVariant = 'error' | 'success';

/**
 * Status message for the whole form (e.g. submit success or a submission/server error),
 * not for a single field. Place it as a direct child of the form.
 * Spacing is owned by the surrounding layout (zx-stack / the form), not this component.
 */
@Component({
  selector: 'zx-form-message',
  standalone: true,
  imports: [TextDirective],
  templateUrl: './zx-form-message.component.html',
  styleUrl: './zx-form-message.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormMessageComponent {
  @Input() variant: ZxFormMessageVariant = 'error';

  @HostBinding('class.zx-form-message--success')
  get isSuccess(): boolean {
    return this.variant === 'success';
  }

  @HostBinding('attr.role')
  get role(): string {
    return this.variant === 'error' ? 'alert' : 'status';
  }
}

import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';

/**
 * Action row of a form (submit/cancel buttons). Place zx-button elements inside.
 */
@Component({
  selector: 'zx-form-actions',
  standalone: true,
  templateUrl: './zx-form-actions.component.html',
  styleUrl: './zx-form-actions.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxFormActionsComponent {
  @Input() align: 'start' | 'center' | 'end' | 'between' = 'end';
  /** Pins the action row to the bottom of the viewport as a footer strip. */
  @Input() sticky = false;

  @HostBinding('class') get hostClass(): string {
    const classes = [`zx-form-actions--${this.align}`];
    if (this.sticky) {
      classes.push('zx-form-actions--sticky');
    }
    return classes.join(' ');
  }
}

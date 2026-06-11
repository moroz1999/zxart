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

  @HostBinding('class') get hostClass(): string {
    return `zx-form-actions--${this.align}`;
  }
}

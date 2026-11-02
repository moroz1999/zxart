import {ChangeDetectionStrategy, Component, HostBinding} from '@angular/core';

/** A vertical list of `zx-step`s: the steps of a process, in order. */
@Component({
  selector: 'zx-steps',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrl: './zx-steps.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStepsComponent {
  @HostBinding('attr.role') readonly role = 'list';
}

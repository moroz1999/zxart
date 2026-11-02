import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxSpinnerComponent} from '../zx-spinner/zx-spinner.component';
import {environment} from '../../../../environments/environment';

/**
 * Where a step stands: `number` shows its number, `done` a check, `failed` an
 * exclamation mark, `running` a spinner; `pending` shows its number and steps
 * back, for a step that waits for another.
 */
export type ZxStepStatus = 'number' | 'done' | 'failed' | 'running' | 'pending';

/** One step of a `zx-steps` list: a status marker beside the projected content. */
@Component({
  selector: 'zx-step',
  standalone: true,
  imports: [SvgIconComponent, ZxSpinnerComponent],
  templateUrl: './zx-step.component.html',
  styleUrl: './zx-step.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxStepComponent {
  @Input() number: number | null = null;
  @Input() status: ZxStepStatus = 'number';
  @HostBinding('attr.role') readonly role = 'listitem';

  constructor(iconReg?: SvgIconRegistryService) {
    iconReg?.loadSvg(`${environment.svgUrl}check.svg`, 'check')?.subscribe();
  }

  @HostBinding('class') get hostClass(): string {
    return `zx-step zx-step--${this.status}`;
  }

  get markerText(): string {
    return this.number === null ? '' : String(this.number);
  }
}

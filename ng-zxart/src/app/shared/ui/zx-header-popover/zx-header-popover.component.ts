import {ChangeDetectionStrategy, Component, HostBinding} from '@angular/core';
import {PopoverAnimation} from '../../animations/popover-animations';

@Component({
  selector: 'zx-header-popover',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrls: ['./zx-header-popover.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [PopoverAnimation],
})
export class ZxHeaderPopoverComponent {
  @HostBinding('@popover') readonly _anim = true;
}

import {animate, style, transition, trigger} from '@angular/animations';
import {Component, HostBinding} from '@angular/core';

@Component({
  selector: 'zx-header-popover',
  standalone: true,
  template: '<ng-content></ng-content>',
  styleUrls: ['./zx-header-popover.component.scss'],
  animations: [
    trigger('popover', [
      transition(':enter', [
        style({opacity: 0, transform: 'translateY(20px)'}),
        animate('180ms cubic-bezier(0.22, 1, 0.36, 1)', style({opacity: 1, transform: 'translateY(0)'})),
      ]),
      transition(':leave', [
        animate('130ms cubic-bezier(0.64, 0, 0.78, 0)', style({opacity: 0, transform: 'translateY(20px)'})),
      ]),
    ]),
  ],
})
export class ZxHeaderPopoverComponent {
  @HostBinding('@popover') readonly _anim = true;
}

import {animate, style, transition, trigger} from '@angular/animations';

export const PopoverAnimation = trigger('popover', [
  transition(':enter', [
    style({opacity: 0, transform: 'translateY(20px)'}),
    animate('180ms cubic-bezier(0.22, 1, 0.36, 1)', style({opacity: 1, transform: 'translateY(0)'})),
  ]),
  transition(':leave', [
    animate('130ms cubic-bezier(0.64, 0, 0.78, 0)', style({opacity: 0, transform: 'translateY(20px)'})),
  ]),
]);

export const DropdownPopoverAnimation = trigger('dropdownPopover', [
  transition(':enter', [
    style({opacity: 0}),
    animate('100ms ease-out', style({opacity: 1})),
  ]),
  transition(':leave', [
    animate('100ms ease-in', style({opacity: 0})),
  ]),
]);

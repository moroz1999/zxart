import {animate, style, transition} from '@angular/animations';

export const FadeInOut = [
  transition(':enter', [
    style({opacity: 0}),
    animate('250ms ease-in-out', style({opacity: 1})),
  ]),
  transition(':leave', [
    animate('150ms ease-in-out', style({opacity: 0})),
  ]),
];

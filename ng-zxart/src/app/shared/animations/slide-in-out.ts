import {animate, state, style, transition} from '@angular/animations';

const outToken = {
  overflow: 'hidden',
  height: 0,
  opacity: 0,
  marginTop: 0,
  marginBottom: 0,
  paddingTop: 0,
  paddingBottom: 0,
};
const inToken = {
  overflow: 'hidden',
  height: '*',
  opacity: 1,
  marginTop: '*',
  marginBottom: '*',
  paddingTop: '*',
  paddingBottom: '*',
};
const inEase = '300ms ease-in-out';
const outEase = '120ms ease-in-out';

export const SlideInOut = [
  transition(':enter', [
    style(outToken),
    animate(inEase, style(inToken)),
  ]),
  transition(':leave', [
    animate(outEase, style(outToken)),
  ]),
];

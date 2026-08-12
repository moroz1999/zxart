import {Directive, HostBinding} from '@angular/core';

/**
 * Layout directive for a responsive screenshots grid.
 *
 * Apply to any block element that contains screenshot cells or their
 * placeholders. Renders a CSS Grid of five thumbnail columns, collapsing to
 * four on tablets and two on phones.
 *
 * Usage:
 *   <div zxScreenshotsGrid>
 *     <figure *ngFor="let file of files" [class.screenshots-grid-featured]="i === 0"></figure>
 *   </div>
 */
@Directive({
  selector: '[zxScreenshotsGrid]',
  standalone: true,
})
export class ZxScreenshotsGridDirective {
  @HostBinding('class.screenshots-grid') readonly hostClass = true;
}
